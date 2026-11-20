<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Data;

use App\Domain\Catalog\Enums\RiskLevel;

/** El resultado del semáforo, con el porqué. */
final readonly class RiskAssessment
{
    /**
     * @param  array<int, string>  $reasons  Qué señales se dispararon
     * @param  array<int, string>  $blockedWords  Lo que impide generar
     */
    public function __construct(
        public RiskLevel $level,
        public array $reasons = [],
        public array $blockedWords = [],
    ) {}

    public function allowsDocument(): bool
    {
        return $this->level->allowsDocument();
    }

    public function requiresAcknowledgement(): bool
    {
        return $this->level->requiresAcknowledgement();
    }

    public function isBlocked(): bool
    {
        return $this->level === RiskLevel::Blocked;
    }
}
