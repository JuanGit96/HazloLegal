<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Services;

use App\Domain\Lawyers\Models\Lawyer;

/**
 * Quién es el abogado que está usando el portal.
 *
 * Existe para que ningún recurso del portal tenga que acordarse de filtrar:
 * se pregunta aquí y se acota la consulta con el resultado. Si esto devuelve
 * null, no hay nada que mostrar, y eso es lo correcto.
 */
final class CurrentLawyer
{
    public function get(): ?Lawyer
    {
        return auth()->user()?->lawyer;
    }

    public function id(): ?int
    {
        return $this->get()?->id;
    }

    /** ¿Su plan le da esta característica? */
    public function allows(string $feature): bool
    {
        return (bool) $this->get()?->subscription?->allows($feature);
    }

    public function hasSubscription(): bool
    {
        return $this->get()?->subscription !== null;
    }
}
