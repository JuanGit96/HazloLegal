<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Data;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\LegalCases\Enums\BudgetRange;
use App\Domain\LegalCases\Enums\CaseUrgency;

final readonly class CaseIntakeData
{
    public function __construct(
        public string $clientName,
        public string $clientEmail,
        public string $clientPhone,
        public ?string $clientCity,
        public PracticeArea $practiceArea,
        public string $title,
        public string $description,
        public CaseUrgency $urgency,
        public ?BudgetRange $budgetRange,
        public bool $hasDocuments = false,
        public ?string $deadlineNote = null,
        public array $utm = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientName: trim((string) $data['client_name']),
            clientEmail: mb_strtolower(trim((string) $data['client_email'])),
            clientPhone: trim((string) $data['client_phone']),
            clientCity: $data['client_city'] ?? null,
            practiceArea: PracticeArea::from($data['practice_area']),
            title: trim((string) $data['title']),
            description: trim((string) $data['description']),
            urgency: CaseUrgency::from($data['urgency']),
            budgetRange: isset($data['budget_range']) && $data['budget_range']
                ? BudgetRange::from($data['budget_range'])
                : null,
            hasDocuments: (bool) ($data['has_documents'] ?? false),
            deadlineNote: $data['deadline_note'] ?? null,
            utm: array_filter([
                'utm_source' => $data['utm_source'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
            ]),
        );
    }
}
