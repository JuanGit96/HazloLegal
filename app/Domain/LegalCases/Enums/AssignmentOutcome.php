<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Enums;

enum AssignmentOutcome: string
{
    /** Este abogado se quedó con el caso. */
    case Won = 'won';
    /** Ya lo tenía asignado (recargó el enlace). */
    case AlreadyYours = 'already_yours';
    /** Otro abogado llegó primero. */
    case TakenByOther = 'taken_by_other';
    /** El abogado rechazó el caso. */
    case Declined = 'declined';
    /** La invitación venció. */
    case Expired = 'expired';
    /** El caso fue anulado por el equipo. */
    case Unavailable = 'unavailable';

    public function isSuccess(): bool
    {
        return in_array($this, [self::Won, self::AlreadyYours], true);
    }
}
