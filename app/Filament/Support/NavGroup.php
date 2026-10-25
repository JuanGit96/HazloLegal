<?php

declare(strict_types=1);

namespace App\Filament\Support;

/**
 * Grupos del menú del panel. Centralizados para que un cambio de nombre
 * no obligue a tocar once recursos.
 */
final class NavGroup
{
    public const OPERATION = 'Operación diaria';

    public const CATALOG = 'Catálogo y precios';

    public const NETWORK = 'Red de abogados';

    public const CONTENT = 'Contenido y SEO';

    public const MARKETING = 'Marketing y leads';

    public const ROADMAP = 'Próximos módulos';

    public const SETTINGS = 'Configuración';

    /** Orden en que aparecen en la barra lateral. */
    public static function ordered(): array
    {
        return [
            self::OPERATION,
            self::CATALOG,
            self::NETWORK,
            self::CONTENT,
            self::MARKETING,
            self::ROADMAP,
            self::SETTINGS,
        ];
    }
}
