<?php

declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Las familias de cookies, que es lo que el visitante autoriza o no.
 *
 * La SIC aplica la Ley 1581 de 2012 a las cookies que permiten identificar a
 * una persona: eso convierte la analítica y la publicidad en tratamiento de
 * datos personales y exige autorización previa, expresa e informada. Las
 * necesarias no se consultan porque sin ellas no hay sesión ni protección
 * CSRF: no son una preferencia, son el sitio funcionando.
 */
enum CookieCategory: string implements HasColor, HasIcon, HasLabel
{
    case Necessary = 'necessary';
    case Analytics = 'analytics';
    case Marketing = 'marketing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Necessary => 'Necesarias',
            self::Analytics => 'Analítica',
            self::Marketing => 'Publicidad',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Necessary => 'gray',
            self::Analytics => 'info',
            self::Marketing => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Necessary => Heroicon::OutlinedLockClosed,
            self::Analytics => Heroicon::OutlinedChartBar,
            self::Marketing => Heroicon::OutlinedMegaphone,
        };
    }

    /** Lo que se le explica al visitante, en una frase. */
    public function description(): string
    {
        return match ($this) {
            self::Necessary => 'Mantienen tu sesión, recuerdan tu pedido y protegen los formularios. Sin ellas el sitio no funciona, así que no se pueden desactivar.',
            self::Analytics => 'Nos dicen qué páginas se leen y dónde se abandona un trámite, para arreglarlo. Son datos de navegación, no el contenido de tu caso.',
            self::Marketing => 'Permiten medir si un anuncio sirvió y no repetirte el mismo. Hoy no las usamos; si algún día las activamos, será solo con tu permiso.',
        };
    }

    /** Las que el visitante decide. Las necesarias no se preguntan. */
    public static function optional(): array
    {
        return [self::Analytics, self::Marketing];
    }

    public function isOptional(): bool
    {
        return $this !== self::Necessary;
    }
}
