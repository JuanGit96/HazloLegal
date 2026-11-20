<?php

declare(strict_types=1);

namespace App\Domain\Orders\Data;

use App\Domain\Orders\Enums\PaymentMethod;

final readonly class OrderDraft
{
    /** @param array<int, string> $addonKeys */
    public function __construct(
        public int $legalDocumentId,
        public array $addonKeys,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public string $customerDocumentType,
        public ?string $customerDocumentNumber,
        public ?string $customerCity,
        public ?string $caseSummary,
        public array $answers = [],
        public bool $acceptsMarketing = false,
        public bool $shareableWithLawyers = false,
        public ?PaymentMethod $paymentMethod = null,
        public array $utm = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            legalDocumentId: (int) $data['legal_document_id'],
            addonKeys: array_values(array_filter((array) ($data['addons'] ?? []))),
            customerName: trim((string) $data['customer_name']),
            customerEmail: mb_strtolower(trim((string) $data['customer_email'])),
            customerPhone: trim((string) $data['customer_phone']),
            customerDocumentType: $data['customer_document_type'] ?? 'CC',
            customerDocumentNumber: $data['customer_document_number'] ?? null,
            customerCity: $data['customer_city'] ?? null,
            caseSummary: $data['case_summary'] ?? null,
            answers: $data['answers'] ?? [],
            acceptsMarketing: (bool) ($data['accepts_marketing'] ?? false),
            shareableWithLawyers: (bool) ($data['shareable_with_lawyers'] ?? false),
            paymentMethod: filled($data['payment_method'] ?? null)
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
