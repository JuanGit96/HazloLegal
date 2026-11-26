<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Lo que se cuenta.
 *
 * Cada una responde a una pregunta que el abogado se hace antes de renovar:
 * ¿me están viendo?, ¿me están buscando?, ¿me llegan casos?, ¿los gano?
 */
enum MetricKind: string implements HasColor, HasIcon, HasLabel
{
    case ProfileView = 'profile_view';
    case DirectoryImpression = 'directory_impression';
    case ContactClick = 'contact_click';
    case ArticleView = 'article_view';
    case PdfPlacement = 'pdf_placement';
    case CaseInvitation = 'case_invitation';
    case CaseApplication = 'case_application';
    case CaseWon = 'case_won';

    public function getLabel(): string
    {
        return match ($this) {
            self::ProfileView => 'Visitas a tu perfil',
            self::DirectoryImpression => 'Apariciones en el directorio',
            self::ContactClick => 'Clics para contactarte',
            self::ArticleView => 'Lecturas de tus artículos',
            self::PdfPlacement => 'Instructivos con tu nombre',
            self::CaseInvitation => 'Casos que te ofrecimos',
            self::CaseApplication => 'Casos a los que te postulaste',
            self::CaseWon => 'Casos que te eligieron',
        };
    }

    /** La misma idea, en una frase, para que la cifra signifique algo. */
    public function explanation(): string
    {
        return match ($this) {
            self::ProfileView => 'Personas que abrieron tu ficha completa.',
            self::DirectoryImpression => 'Veces que saliste en un listado de búsqueda por área o ciudad.',
            self::ContactClick => 'Pulsaron tu botón de WhatsApp o de correo.',
            self::ArticleView => 'Lecturas de las guías que firmas tú.',
            self::PdfPlacement => 'Documentos entregados con tus datos dentro. Ese PDF se imprime y circula.',
            self::CaseInvitation => 'Casos de tu área que te mandamos por correo.',
            self::CaseApplication => 'De esos, a los que decidiste postularte.',
            self::CaseWon => 'Casos en los que el cliente te eligió a ti.',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ProfileView => 'info',
            self::DirectoryImpression => 'gray',
            self::ContactClick => 'primary',
            self::ArticleView => 'info',
            self::PdfPlacement => 'warning',
            self::CaseInvitation => 'gray',
            self::CaseApplication => 'warning',
            self::CaseWon => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::ProfileView => Heroicon::OutlinedEye,
            self::DirectoryImpression => Heroicon::OutlinedListBullet,
            self::ContactClick => Heroicon::OutlinedCursorArrowRays,
            self::ArticleView => Heroicon::OutlinedBookOpen,
            self::PdfPlacement => Heroicon::OutlinedDocumentText,
            self::CaseInvitation => Heroicon::OutlinedInbox,
            self::CaseApplication => Heroicon::OutlinedHandRaised,
            self::CaseWon => Heroicon::OutlinedTrophy,
        };
    }

    /** Las que miden que a uno lo vean. Son las que sostienen la suscripción. */
    public static function visibility(): array
    {
        return [self::DirectoryImpression, self::ProfileView, self::ContactClick, self::ArticleView, self::PdfPlacement];
    }
}
