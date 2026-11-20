<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Resumen';

    protected static ?string $title = 'Tu resumen';

    public function getSubheading(): ?string
    {
        $name = auth()->user()?->lawyer?->name;

        return $name ? "Hola, {$name}. Esto es lo que ha pasado con tu presencia en HazloLegal." : null;
    }
}
