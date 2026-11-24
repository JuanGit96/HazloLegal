<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data;

use App\Domain\Shared\Enums\CookieCategory;

/**
 * Lo que el visitante decidió sobre las cookies.
 *
 * Existe para que nadie tenga que acordarse de la forma del JSON que viaja
 * en la cookie: se pregunta `allows()` y se acabó.
 */
final readonly class CookieConsentChoice
{
    public function __construct(
        public bool $analytics = false,
        public bool $marketing = false,
        public ?string $token = null,
        public ?string $policyVersion = null,
        public bool $answered = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            analytics: (bool) ($data['analytics'] ?? false),
            marketing: (bool) ($data['marketing'] ?? false),
            token: isset($data['token']) ? (string) $data['token'] : null,
            policyVersion: isset($data['v']) ? (string) $data['v'] : null,
            answered: true,
        );
    }

    /** Nadie ha contestado todavía: solo corren las necesarias. */
    public static function pending(): self
    {
        return new self;
    }

    public function allows(CookieCategory|string $category): bool
    {
        $value = $category instanceof CookieCategory ? $category->value : $category;

        return match ($value) {
            CookieCategory::Necessary->value => true,
            CookieCategory::Analytics->value => $this->analytics,
            CookieCategory::Marketing->value => $this->marketing,
            default => false,
        };
    }

    /** ¿Hay que volver a preguntar? Sin respuesta, o respuesta a otro texto. */
    public function needsAsking(string $currentVersion): bool
    {
        return ! $this->answered || $this->policyVersion !== $currentVersion;
    }

    public function toArray(): array
    {
        return [
            'analytics' => $this->analytics,
            'marketing' => $this->marketing,
            'token' => $this->token,
            'v' => $this->policyVersion,
        ];
    }
}
