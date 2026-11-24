<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Documents\Services\DocumentReviewer;
use App\Domain\Documents\Services\InstructionsPdfBuilder;
use App\Domain\Documents\Services\LegalDocumentPdfBuilder;
use App\Domain\Documents\Services\SignatureService;
use App\Domain\Orders\Data\OrderDraft;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Mail\OrderDeliveredMail;
use App\Domain\Orders\Mail\OrderPaidMail;
use App\Domain\Orders\Mail\OrderReceivedMail;
use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Services\LeadCaptureService;
use App\Domain\Shared\Services\ReferenceGenerator;
use App\Jobs\PrepareOrderDeliverables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class OrderService
{
    public function __construct(
        private readonly ReferenceGenerator $references,
        private readonly PricingService $pricing,
        private readonly DocumentReviewer $reviewer,
        private readonly LeadCaptureService $leads,
        private readonly LegalDocumentPdfBuilder $documents,
        private readonly InstructionsPdfBuilder $instructions,
        private readonly SignatureService $signatures,
    ) {}

    public function place(OrderDraft $draft): Order
    {
        $document = LegalDocument::query()
            ->with('availableAddons')
            ->findOrFail($draft->legalDocumentId);

        $quote = $this->pricing->quote($document, $draft->addonKeys);

        // La revisión corre antes de guardar: lo que se almacena es el texto
        // ya corregido, porque es el que va a terminar en el documento.
        $review = $this->reviewer->review((string) $draft->caseSummary);

        $order = DB::transaction(function () use ($draft, $document, $quote, $review) {
            /** @var Order $order */
            $order = Order::query()->create([
                'reference' => $this->references->for(Order::class, 'HL'),
                // La referencia es correlativa y por tanto adivinable: la
                // llave de acceso es lo que impide recorrer pedidos ajenos.
                'access_token' => bin2hex(random_bytes(16)),
                'legal_document_id' => $document->id,
                'status' => $quote->isFree() ? OrderStatus::Paid : OrderStatus::AwaitingPayment,
                'tier' => $quote->tier,
                'speed' => $quote->hasAddon('express') ? 'express' : 'standard',

                'customer_name' => $draft->customerName,
                'customer_email' => $draft->customerEmail,
                'customer_phone' => $draft->customerPhone,
                'customer_document_type' => $draft->customerDocumentType,
                'customer_document_number' => $draft->customerDocumentNumber,
                'customer_city' => $draft->customerCity,

                'case_summary' => $review->corrected,
                'answers' => $draft->answers,
                'ai_review' => $review->toArray(),

                'base_amount' => $quote->baseAmount,
                'addons_amount' => $quote->addonsAmount,
                'discount_amount' => $quote->discountAmount,
                'amount' => $quote->total,

                'accepts_marketing' => $draft->acceptsMarketing,
                'shareable_with_lawyers' => $draft->shareableWithLawyers,
                'wants_signature' => $quote->hasAddon('firma_digital'),

                'payment_method' => $draft->paymentMethod,
                'paid_at' => $quote->isFree() ? now() : null,
                'promised_at' => $quote->promisedAt(),
                ...$draft->utm,
            ]);

            foreach ($quote->addons as $addon) {
                $order->addons()->create([
                    'service_addon_id' => $addon->id,
                    'key' => $addon->key,
                    'name' => $addon->name,
                    'price' => $addon->priceFor($document),
                ]);
            }

            $document->incrementQuietly('orders_count');

            return $order;
        });

        // El lead es la contrapartida del documento gratuito: sin él, regalar
        // el trámite sería solo un costo.
        $this->leads->capture([
            'email' => $draft->customerEmail,
            'name' => $draft->customerName,
            'phone' => $draft->customerPhone,
            'city' => $draft->customerCity,
            'source' => $quote->isFree() ? 'free_document' : 'paid_document',
            'practice_area' => $document->practice_area->value,
            'case_summary' => $review->corrected,
            'order_id' => $order->id,
            'legal_document_id' => $document->id,
            'consent_marketing' => $draft->acceptsMarketing,
            'consent_share_with_lawyers' => $draft->shareableWithLawyers,
            'ip_address' => request()?->ip(),
        ]);

        // Sin nada que cobrar, el pedido ya está pagado y entra a la cola de
        // entrega en el mismo momento.
        if ($order->paid_at !== null) {
            $this->scheduleDelivery($order);
        }

        Mail::to($order->customer_email)->send(new OrderReceivedMail($order->refresh()));

        return $order->load(['document', 'addons']);
    }

    public function confirmPayment(Order $order, PaymentMethod $method, ?string $reference = null): Order
    {
        if ($order->paid_at !== null) {
            return $order;
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'payment_method' => $method,
            'payment_reference' => $reference ?? 'DEMO-'.strtoupper(bin2hex(random_bytes(4))),
            'paid_at' => now(),
            // El reloj arranca con el pago, no con el pedido.
            'promised_at' => now()->addHours(
                $order->isExpress()
                    ? (int) $order->document->express_delivery_hours
                    : (int) $order->document->delivery_hours
            ),
        ]);

        $order->refresh();

        $this->scheduleDelivery($order);

        Mail::to($order->customer_email)->send(new OrderPaidMail($order->refresh()));

        return $order;
    }

    /**
     * Suma un adicional a un pedido que ya existe.
     *
     * Es la venta más fácil del negocio: alguien que acaba de recibir su
     * documento gratis ya confía en que funciona, y el instructivo o la
     * entrega inmediata se deciden en un clic. Se cobra aparte y el precio
     * queda congelado igual que en la compra original.
     */
    public function addAddon(Order $order, string $key): Order
    {
        $order->loadMissing(['document.availableAddons', 'addons']);

        if ($order->hasAddon($key)) {
            return $order;
        }

        $addon = $order->document->availableAddons->firstWhere('key', $key);

        if (! $addon instanceof ServiceAddon) {
            return $order;
        }

        // La incompatibilidad se comprueba aquí y no solo en la vista: el
        // enlace de «pasar a entrega inmediata» se puede reenviar, y sumar
        // express a un pedido que escribe un abogado sería cobrar por una
        // prisa que no vamos a cumplir.
        if ($this->addonClashes($order, $addon)) {
            return $order;
        }

        $price = $addon->priceFor($order->document);

        DB::transaction(function () use ($order, $addon, $price) {
            $order->addons()->create([
                'service_addon_id' => $addon->id,
                'key' => $addon->key,
                'name' => $addon->name,
                'price' => $price,
            ]);

            $order->update([
                'addons_amount' => (int) $order->addons_amount + $price,
                'amount' => (int) $order->amount + $price,
                // Si ya estaba pagado y el adicional cuesta, vuelve a cobro.
                'status' => $price > 0 ? OrderStatus::AwaitingPayment : $order->status,
                'paid_at' => $price > 0 ? null : $order->paid_at,
            ]);
        });

        return $order->refresh()->load(['document', 'addons']);
    }

    /** ¿Se le puede sumar este adicional al pedido tal como está hoy? */
    public function canAddAddon(Order $order, string $key): bool
    {
        $order->loadMissing(['document.availableAddons', 'addons']);

        if ($order->hasAddon($key)) {
            return false;
        }

        $addon = $order->document->availableAddons->firstWhere('key', $key);

        return $addon instanceof ServiceAddon && ! $this->addonClashes($order, $addon);
    }

    /**
     * Choca con una familia que el pedido ya lleva.
     *
     * Las líneas del pedido guardan la clave, no la familia, así que se
     * resuelven contra el catálogo del trámite —que ya viene cargado— en vez
     * de pedir la relación línea a línea.
     */
    private function addonClashes(Order $order, ServiceAddon $addon): bool
    {
        $puestas = $order->addons
            ->map(fn ($linea) => $order->document->availableAddons->firstWhere('key', $linea->key))
            ->filter()
            ->values();

        return in_array($addon->kind->value, PricingService::blockedKinds($puestas), true);
    }

    /**
     * Pone el pedido en la cola de entrega.
     *
     * Con entrega inmediata sale ya; sin ella, se respeta el plazo que el
     * trámite tiene configurado. Entregarlo todo al instante haría que el
     * adicional de inmediatez no significara nada, y quien lo pagó tiene
     * derecho a que signifique algo.
     */
    public function scheduleDelivery(Order $order): Order
    {
        if ($order->isExpress()) {
            return $this->deliver($order);
        }

        $order->update(['status' => OrderStatus::InReview]);

        PrepareOrderDeliverables::dispatch($order->id)
            ->delay($order->promised_at ?? now()->addHours((int) $order->document->delivery_hours));

        return $order->refresh();
    }

    /**
     * Genera los PDF del pedido y lo marca entregado.
     *
     * Un fallo aquí no puede dejar el pedido en un limbo silencioso: se
     * registra, el estado no avanza y el panel lo muestra en el filtro
     * «pagado y sin documento generado» para que alguien lo retome.
     */
    public function deliver(Order $order): Order
    {
        try {
            $this->documents->build($order);

            // Los trámites de pago llevan instructivo incluido; en los
            // gratuitos es un adicional que se contrata.
            if ($order->hasAddon('instructivo') || ! $order->isFree()) {
                $this->instructions->build($order);
            }

            // Si contrató la firma, el enlace tiene que existir antes de
            // avisarle: el correo de entrega lo lleva.
            if ($order->hasAddon('firma_digital') && ! $order->isSigned()) {
                $this->signatures->request($order);
            }

            $order->update([
                'status' => OrderStatus::Delivered,
                'delivered_at' => now(),
            ]);

            Mail::to($order->customer_email)->send(new OrderDeliveredMail($order->refresh()));
        } catch (\Throwable $e) {
            report($e);
        }

        return $order->refresh();
    }

    /** Alias para el panel: «regenerar» es volver a entregar. */
    public function prepareDeliverables(Order $order): Order
    {
        return $this->deliver($order);
    }

    public function markInReview(Order $order): Order
    {
        $order->update(['status' => OrderStatus::InReview]);

        return $order->refresh();
    }

    public function markDelivered(Order $order, ?string $filePath = null): Order
    {
        $order->update([
            'status' => OrderStatus::Delivered,
            'delivered_at' => now(),
            'delivered_file_path' => $filePath ?? $order->delivered_file_path,
        ]);

        return $order->refresh();
    }

    public function findByReference(string $reference): ?Order
    {
        return Order::query()
            ->with(['document', 'addons', 'assignedLawyer', 'featuredLawyer'])
            ->where('reference', $reference)
            ->first();
    }
}
