<?php

declare(strict_types=1);

namespace App\Domain\Orders\Data;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Orders\Enums\PaymentMethod;

final readonly class OrderDraft
{
    public function __construct(
        public int $legalDocumentId,
        public int $servicePlanId,
        public DeliverySpeed $speed,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $customerDocumentType,
        public ?string $customerDocumentNumber,
        public ?string $customerCity,
        public ?string $caseSummary,
        public array $answers = [],
        public bool $wantsSignature = false,
        public ?PaymentMethod $paymentMethod = null,
        public array $utm = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            legalDocumentId: (int) $data['legal_document_id'],
            servicePlanId: (int) $data['service_plan_id'],
            speed: DeliverySpeed::from($data['speed'] ?? DeliverySpeed::Standard->value),
            customerName: trim((string) $data['customer_name']),
            customerEmail: mb_strtolower(trim((string) $data['customer_email'])),
            customerPhone: trim((string) $data['customer_phone']),
            customerDocumentType: $data['customer_document_type'] ?? 'CC',
            customerDocumentNumber: $data['customer_document_number'] ?? null,
            customerCity: $data['customer_city'] ?? null,
            caseSummary: $data['case_summary'] ?? null,
            answers: $data['answers'] ?? [],
            wantsSignature: (bool) ($data['wants_signature'] ?? false),
            paymentMethod: isset($data['payment_method']) && $data['payment_method']
                ? PaymentMethod::from($data['payment_method'])
                : null,
            utm: array_filter([
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
            ]),
        );
    }
}
