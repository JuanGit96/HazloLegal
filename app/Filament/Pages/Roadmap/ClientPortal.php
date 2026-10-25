<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ClientPortal extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Portal del cliente';

    protected static ?int $navigationSort = 8;

    protected static ?string $title = 'Portal del cliente';

    public function summary(): string
    {
        return 'Una zona donde el cliente sigue su trámite, descarga sus documentos y pide correcciones sin tener que escribir por WhatsApp.';
    }

    public function why(): string
    {
        return 'Reduce las preguntas de «¿cómo va mi trámite?», que son el mayor consumo de tiempo de soporte. Y guardar el historial de documentos facilita que el cliente vuelva a comprar.';
    }

    public function priority(): string
    {
        return 'baja';
    }

    public function effort(): string
    {
        return '~2 semanas';
    }

    public function scope(): array
    {
        return [
            'Acceso sin contraseña, por enlace mágico al correo (menos fricción que un registro).',
            'Estado del trámite con línea de tiempo y la fecha comprometida visible.',
            'Descarga de los documentos entregados, con historial de versiones.',
            'Subir documentos que falten (orden médica, desprendibles, cédula).',
            'Pedir una corrección bajo la garantía, desde el propio pedido.',
            'Historial de todos sus trámites y recompra en un clic.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Página pública de seguimiento por referencia (`/pedido/HL-...`), que ya funciona sin login.',
            'Formulario de reclamación de garantía.',
        ];
    }

    public function dependencies(): array
    {
        return ['Generación y entrega de PDF.'];
    }
}
