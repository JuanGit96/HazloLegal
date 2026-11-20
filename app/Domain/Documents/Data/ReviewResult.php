<?php

declare(strict_types=1);

namespace App\Domain\Documents\Data;

/** Lo que la revisión automática encontró y corrigió en el texto del cliente. */
final readonly class ReviewResult
{
    /**
     * @param array<int, string> $issues Qué se corrigió, en lenguaje del cliente
     * @param array<int, string> $warnings Lo que el cliente debería revisar él mismo
     */
    public function __construct(
        public string $original,
        public string $corrected,
        public array $issues = [],
        public array $warnings = [],
        public bool $usedAi = false,
    ) {}

    public function changed(): bool
    {
        return trim($this->original) !== trim($this->corrected);
    }

    public function hasFindings(): bool
    {
        return $this->issues !== [] || $this->warnings !== [];
    }

    public function toArray(): array
    {
        return [
            'corrected' => $this->corrected,
            'issues' => $this->issues,
            'warnings' => $this->warnings,
            'used_ai' => $this->usedAi,
            'reviewed_at' => now()->toIso8601String(),
        ];
    }
}
