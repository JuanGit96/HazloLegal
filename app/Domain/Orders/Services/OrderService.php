<?php

declare(strict_types=1);

namespace App\Domain\Orders\Services;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use App\Domain\Catalog\Services\PricingService;
use App\Domain\Orders\Data\OrderDraft;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Mail\OrderPaidMail;
use App\Domain\Orders\Mail\OrderReceivedMail;
use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Services\ReferenceGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class OrderService
{
    public function __construct(
        private readonly ReferenceGenerator $references,
        private readonly PricingService $pricing,
    ) {}

    public function place(OrderDraft $draft): Order
    {
        $document = LegalDocument::query()->findOrFail($draft->legalDocumentId);
        $plan = ServicePlan::query()->findOrFail($draft->servicePlanId);
        $quote = $this->pricing->quote($document, $plan, $draft->speed);

        $order = DB::transaction(fn () => Order::query()->create([
            'reference' => $this->references->for(Order::class, 'HL'),
            'legal_document_id' => $document->id,
            'service_plan_id' => $plan->id,
            'speed' => $quote->speed,
            'status' => OrderStatus::AwaitingPayment,
            'customer_name' => $draft->customerName,
            'customer_email' => $draft->customerEmail,
            'customer_phone' => $draft->customerPhone,
            'customer_document_type' => $draft->customerDocumentType,
            'customer_document_number' => $draft->customerDocumentNumber,
            'customer_city' => $draft->customerCity,
            'case_summary' => $draft->caseSummary,
            'answers' => $draft->answers,
            'wants_signature' => $draft->wantsSignature,
            'amount' => $quote->amount ?? 0,
            'payment_method' => $draft->paymentMethod,
            'promised_at' => $quote->promisedAt(),
            ...$draft->utm,
        ]));

        $document->incrementQuietly('orders_count');

        Mail::to($order->customer_email)->send(new OrderReceivedMail($order));

        return $order->load(['document', 'plan']);
    }

    /**
     * En producción lo llamará el webhook de la pasarela. En el prototipo lo
     * dispara el propio checkout para que el flujo se pueda recorrer completo.
     */
    public function confirmPayment(Order $order, PaymentMethod $method, ?string $reference = null): Order
    {
        $hours = $order->plan->deliveryHoursFor($order->speed);

        $order->update([
            'status' => OrderStatus::Paid,
            'payment_method' => $method,
            'payment_reference' => $reference ?? 'DEMO-'.strtoupper(bin2hex(random_bytes(4))),
            'paid_at' => now(),
            // El reloj de la promesa arranca cuando entra la plata, no antes.
            'promised_at' => $hours ? now()->addHours($hours) : $order->promised_at,
        ]);

        $order->refresh();

        Mail::to($order->customer_email)->send(new OrderPaidMail($order));

        return $order;
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
            ->with(['document', 'plan', 'assignedLawyer'])
            ->where('reference', $reference)
            ->first();
    }
}
