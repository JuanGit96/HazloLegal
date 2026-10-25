<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use App\Filament\Support\NavGroup;
use Filament\Pages\Page;

/**
 * Base de las páginas de hoja de ruta.
 *
 * Son marcadores de posición a propósito: reservan el lugar en el menú y
 * documentan qué debe hacer cada módulo, para que la decisión de construirlo
 * no empiece de cero. No tienen lógica.
 */
abstract class RoadmapPage extends Page
{
    protected string $view = 'filament.pages.roadmap';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::ROADMAP;

    /** Resumen de una línea: qué resuelve el módulo. */
    abstract public function summary(): string;

    /** Por qué vale la pena construirlo, en términos de negocio. */
    abstract public function why(): string;

    /** @return array<int, string> Lo que el módulo debería hacer. */
    abstract public function scope(): array;

    /** @return array<int, string> De qué depende para poder empezar. */
    public function dependencies(): array
    {
        return [];
    }

    /** alta | media | baja */
    public function priority(): string
    {
        return 'media';
    }

    public function effort(): string
    {
        return 'Por estimar';
    }

    /** Qué parte del sistema ya está lista y lo deja a medio camino. */
    public function alreadyInPlace(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        return [
            'summary' => $this->summary(),
            'why' => $this->why(),
            'scope' => $this->scope(),
            'dependencies' => $this->dependencies(),
            'priority' => $this->priority(),
            'effort' => $this->effort(),
            'alreadyInPlace' => $this->alreadyInPlace(),
        ];
    }
}
