<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use App\Domain\Catalog\Services\CatalogService;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Orders\Data\OrderDraft;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Seo\Services\SeoManager;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Embudo de 4 pasos: plan → documento → configuración y datos → pago.
 * Las páginas de pasos son indexables pero no interesan a Google, así que
 * van con noindex para no diluir las landings de servicio.
 */
class CheckoutController
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly PricingService $pricing,
        private readonly OrderService $orders,
        private readonly SeoManager $seo,
    ) {}

    public function plans(): View
    {
        return view('checkout.plans', [
            'meta' => $this->seo->page(
                title: 'Elige tu plan | HazloLegal',
                description: 'Documento listo para usar, documento con asesoría o representación para un caso complejo.',
                canonical: route('checkout.plans'),
                noindex: true,
            ),
            'plans' => $this->catalog->plans(),
            'pricing' => $this->pricing,
        ]);
    }

    /** Paso 1: el cliente ya eligió plan, ahora elige qué documento necesita. */
    public function documents(ServicePlan $plan): View|RedirectResponse
    {
        if ($plan->is_quote_only) {
            return redirect()->route('cases.create');
        }

        return view('checkout.documents', [
            'meta' => $this->seo->page(
                title: "Elige tu trámite · {$plan->name} | HazloLegal",
                description: 'Selecciona el documento que necesitas y continúa con tus datos.',
                canonical: route('checkout.documents', $plan->key->value),
                noindex: true,
            ),
            'plan' => $plan,
            'grouped' => $this->catalog->groupedByArea(),
            'pricing' => $this->pricing,
            'step' => 1,
        ]);
    }

    /** Paso 2 y 3: velocidad de entrega (estándar vs express) y datos del caso. */
    public function configure(ServicePlan $plan, LegalDocument $document, Request $request): View|RedirectResponse
    {
        if ($plan->is_quote_only) {
            return redirect()->route('cases.create');
        }

        abort_unless($document->is_active, 404);

        $requested = DeliverySpeed::tryFrom((string) $request->query('velocidad')) ?? DeliverySpeed::Standard;

        return view('checkout.configure', [
            'meta' => $this->seo->page(
                title: "Pedir {$document->name} · {$plan->name} | HazloLegal",
                description: 'Confirma la velocidad de entrega y cuéntanos tu caso.',
                canonical: route('checkout.configure', [$plan->key->value, $document->slug]),
                noindex: true,
            ),
            'plan' => $plan,
            'document' => $document,
            'selectedSpeed' => $document->allowsSpeed($requested) ? $requested : DeliverySpeed::Standard,
            'standardQuote' => $this->pricing->quote($document, $plan, DeliverySpeed::Standard),
            'expressQuote' => $document->available_express && $plan->hasSpeedChoice()
                ? $this->pricing->quote($document, $plan, DeliverySpeed::Express)
                : null,
            'upgradeCost' => $this->pricing->expressUpgradeCost($plan),
            'paymentMethods' => PaymentMethod::cases(),
            'pricing' => $this->pricing,
            'step' => 2,
        ]);
    }

    public function store(StoreOrderRequest $request, ServicePlan $plan, LegalDocument $document): RedirectResponse
    {
        $order = $this->orders->place(OrderDraft::fromArray([
            ...$request->validated(),
            'legal_document_id' => $document->id,
            'service_plan_id' => $plan->id,
            'answers' => [
                'documento' => $document->name,
                'plan' => $plan->name,
                'firma_electronica' => $request->boolean('wants_signature') ? 'Sí' : 'No',
            ],
            ...$request->only(['utm_source', 'utm_medium', 'utm_campaign']),
        ]));

        return redirect()
            ->route('checkout.payment', $order->reference)
            ->with('status', 'Tu pedido quedó registrado. Falta el último paso.');
    }

    /** Paso 4: pago. En el prototipo no se cobra, sólo se simula. */
    public function payment(Order $order): View|RedirectResponse
    {
        if ($order->paid_at !== null) {
            return redirect()->route('checkout.confirmation', $order->reference);
        }

        return view('checkout.payment', [
            'meta' => $this->seo->page(
                title: "Pago de tu pedido {$order->reference} | HazloLegal",
                description: 'Último paso para que tu documento entre a la fila de redacción.',
                canonical: route('checkout.payment', $order->reference),
                noindex: true,
            ),
            'order' => $order->load(['document', 'plan']),
            'paymentMethods' => PaymentMethod::cases(),
            'step' => 3,
        ]);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', \Illuminate\Validation\Rule::enum(PaymentMethod::class)],
        ]);

        if ($order->paid_at === null) {
            $this->orders->confirmPayment($order, PaymentMethod::from($validated['payment_method']));
        }

        return redirect()
            ->route('checkout.confirmation', $order->reference)
            ->with('status', '¡Pago confirmado! Tu documento ya entró a la fila.');
    }

    public function confirmation(Order $order): View
    {
        return view('checkout.confirmation', [
            'meta' => $this->seo->page(
                title: "Pedido {$order->reference} confirmado | HazloLegal",
                description: 'Resumen y seguimiento de tu pedido.',
                canonical: route('checkout.confirmation', $order->reference),
                noindex: true,
            ),
            'order' => $order->load(['document', 'plan', 'assignedLawyer']),
            'step' => 4,
        ]);
    }
}
