<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Data;

use App\Domain\LegalCases\Enums\AssignmentOutcome;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;

final readonly class AssignmentResult
{
    public function __construct(
        public AssignmentOutcome $outcome,
        public LegalCase $case,
        public CaseOffer $offer,
        /** Abogados que perdieron la carrera y deben recibir aviso. */
        public array $supersededOfferIds = [],
    ) {}

    public function won(): bool
    {
        return $this->outcome === AssignmentOutcome::Won;
    }
}
