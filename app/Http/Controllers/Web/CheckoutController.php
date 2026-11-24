<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\AddonOfferService;
use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\DocumentFieldResolver;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Orders\Data\OrderDraft;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Models\Order;
use App\Domain\Documents\Services\LegalDocumentPdfBuilder;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Payments\Services\WompiGateway;
use App\Domain\Seo\Services\SeoManager;
use App\Domain\Settings\Services\SettingsService;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * El embudo de compra.
 *
 * Tres pasos: elegir trámite, armarlo con adicionales, pagar. Si el trámite
 * es gratuito y no se añade nada, el paso de pago se salta por completo —
 * meter una pantalla de pago de cero pesos es la forma más rápida de perder
 * a alguien que venía a por algo gratis.
 */
class CheckoutController
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly PricingService $pricing,
        private readonly AddonOfferService $offers,
        private readonly OrderService $orders,
        private readonly SeoManager $seo,
        private readonly SettingsService $settings,
        private readonly WompiGateway $wompi,
        private readonly DocumentFieldResolver $fields,
    ) {}

    public function plans(): View
    {
        return view('checkout.documents', [
            'meta' => $this->seo->page(
                title: 'Elige tu trámite | HazloLegal',
                description: 'Selecciona el documento que necesitas.',
                canonical: route('checkout.plans'),
                noindex: true,
            ),
            'grouped' => $this->catalog->groupedByArea(),
            'pricing' => $this->pricing,
            'step' => 1,
        ]);
    }

    public function documents(): View
    {
        return $this->plans();
    }

    /** Paso 2: armar el trámite con adicionales y dejar los datos. */
    public function configure(LegalDocument $document, Request $request): View|RedirectResponse
    {
        abort_unless($document->is_active, 404);

        // Un trámite que no produce documento por sí solo no tiene nada que
        // configurar: ofrecerle «entrega inmediata» o «instructivo» a algo que
        // todavía no existe no significa nada. Va directo a cotización.
        if (! $document->allowsSelfService()) {
            return redirect()->route('cases.create', [
                'area' => $document->practice_area->value,
                'tramite' => $document->slug,
            ]);
        }

        $selected = $this->resolveSelection($document, $request);
        $quote = $this->pricing->quote($document, $selected);

        return view('checkout.configure', [
            'meta' => $this->seo->page(
                title: "Pedir {$document->name} | HazloLegal",
                description: 'Arma tu trámite y cuéntanos tu caso.',
                canonical: route('checkout.configure', $document->slug),
                noindex: true,
            ),
            'document' => $document,
            'fields' => $this->fields->for($document),
            'offers' => $this->offers->offersFor($document, $selected),
            'quote' => $quote,
            'selected' => $selected,
            'showDiscounts' => $this->settings->bool('promo_enabled', true),
            'aiHook' => $this->settings->bool('hook_ai_enabled', true),
            'step' => 2,
        ]);
    }

    public function store(StoreOrderRequest $request, LegalDocument $document): RedirectResponse
    {
        abort_unless($document->allowsSelfService(), 404);

        $order = $this->orders->place(OrderDraft::fromArray([
            ...$request->validated(),
            'legal_document_id' => $document->id,
            'answers' => [
                'documento' => $document->name,
                'campos' => $this->fields->capture($document, (array) $request->input('fields', [])),
            ],
            ...$request->only(['utm_source', 'utm_medium', 'utm_campaign']),
        ]));

        // Quien acaba de hacer el pedido no tiene que llevar el token en la
        // URL: la sesión ya prueba que es suyo.
        $request->session()->push('orders', $order->reference);

        // Gratis y sin adicionales: no hay nada que cobrar.
        if ($order->isFree()) {
            return redirect()
                ->to($order->trackingUrl())
                ->with('status', 'Tu documento está listo.');
        }

        return redirect()->route('checkout.payment', $order->reference);  // la sesión ya autoriza
    }

    public function payment(Order $order, Request $request): View|RedirectResponse
    {
        $this->authorizeOrder($order, $request);

        if ($order->paid_at !== null) {
            return redirect()->to($order->trackingUrl());
        }

        $order->load(['document', 'addons']);

        // Se abre un intento nuevo en cada visita: Wompi no admite dos
        // transacciones con la misma referencia, y así un pago rechazado se
        // puede reintentar sin inventar otro pedido.
        $transaction = $this->wompi->isEnabled()
            ? $this->wompi->open($order, (int) $order->amount, (string) $order->customer_email)
            : null;

        return view('checkout.payment', [
            'meta' => $this->seo->page(
                title: "Pago de tu pedido {$order->reference} | HazloLegal",
                description: 'Último paso.',
                canonical: route('checkout.payment', $order->reference),
                noindex: true,
            ),
            'order' => $order,
            'paymentMethods' => PaymentMethod::cases(),
            'wompi' => $this->wompi,
            'transaction' => $transaction,
            // Una sola oferta, justo antes de pagar: es el punto de mayor
            // intención del embudo y la decisión ya está tomada, así que
            // sumar algo concreto cuesta poco esfuerzo mental.
            'bump' => $this->settings->bool('promo_upsell_enabled', true)
                ? $this->offers->nextBestOffer($order->document, $order->addons->pluck('key'))
                : null,
            'checkoutFields' => $transaction
                ? $this->wompi->checkoutFields($transaction, route('wompi.return', $transaction->reference))
                : [],
            'step' => 3,
        ]);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order, $request);

        $validated = $request->validate([
            'payment_method' => ['required', \Illuminate\Validation\Rule::enum(PaymentMethod::class)],
        ]);

        $this->orders->confirmPayment($order, PaymentMethod::from($validated['payment_method']));

        return redirect()
            ->to($order->trackingUrl())
            ->with('status', '¡Pago confirmado!');
    }

    public function confirmation(Order $order, Request $request): View
    {
        $this->authorizeOrder($order, $request);

        return view('checkout.confirmation', [
            'meta' => $this->seo->page(
                title: "Pedido {$order->reference} | HazloLegal",
                description: 'Resumen de tu pedido.',
                canonical: route('checkout.confirmation', $order->reference),
                noindex: true,
            ),
            'order' => $order->load(['document', 'addons', 'featuredLawyer']),
            'nextOffer' => $this->offers->nextBestOffer($order->document, $order->addons->pluck('key')),
            'step' => 4,
        ]);
    }

    /** Sumar un adicional a un pedido ya hecho. */
    public function addAddon(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order, $request);

        $validated = $request->validate([
            'addon' => ['required', 'string', 'max:40'],
        ]);

        // Decirle «listo» a quien pidió algo que no se puede cumplir sería
        // peor que negarlo: se iría creyendo que lo tiene.
        if (! $this->orders->canAddAddon($order, $validated['addon'])) {
            return redirect()->to($order->trackingUrl())
                ->with('status', 'Ese adicional no se puede sumar a este pedido. Si quieres cambiarlo, escríbenos y lo ajustamos.');
        }

        $order = $this->orders->addAddon($order, $validated['addon']);

        if ($order->paid_at === null) {
            return redirect()->route('checkout.payment', $order->reference)
                ->with('status', 'Lo agregamos a tu pedido.');
        }

        return redirect()->to($order->trackingUrl())->with('status', 'Lo agregamos a tu pedido.');
    }

    /** Descarga del documento o del instructivo, siempre con llave. */
    public function download(Order $order, string $kind, Request $request): BinaryFileResponse
    {
        $this->authorizeOrder($order, $request);

        $path = match ($kind) {
            'documento' => $order->document_path,
            'instructivo' => $order->instructions_path,
            default => null,
        };

        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        $name = $kind === 'instructivo'
            ? 'instructivo-'.$order->reference.'.pdf'
            : LegalDocumentPdfBuilder::fileName($order->loadMissing('document'));

        return response()->download(Storage::disk('local')->path($path), $name);
    }

    /**
     * Quién puede ver un pedido.
     *
     * La referencia es correlativa, así que no sirve de credencial: hace
     * falta la llave del enlace o haber hecho el pedido en esta sesión.
     */
    private function authorizeOrder(Order $order, Request $request): void
    {
        $mine = in_array($order->reference, (array) $request->session()->get('orders', []), true);

        abort_unless($mine || $order->grantsAccessWith($request->input('t')), 403);
    }

    /**
     * Qué adicionales llegan marcados.
     *
     * Si el usuario ya tocó algo, manda su elección. Si llega limpio, se
     * aplican los premarcados de la ficha. Un premarcado solo es legítimo si
     * se ve, se explica y se puede quitar de un clic: todo eso se cumple en
     * la vista.
     */
    private function resolveSelection(LegalDocument $document, Request $request): array
    {
        if ($request->has('addons')) {
            return array_values(array_filter((array) $request->input('addons', [])));
        }

        if ($request->filled('extra')) {
            return [$request->string('extra')->toString()];
        }

        return $this->offers->preselectedKeys($document);
    }
}
