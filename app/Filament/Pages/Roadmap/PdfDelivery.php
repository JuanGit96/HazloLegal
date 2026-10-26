<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class PdfDelivery extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static ?string $navigationLabel = 'Generación y entrega de PDF';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Generación y entrega del PDF';

    public function summary(): string
    {
        return 'Convertir el documento aprobado en un PDF con la identidad de HazloLegal y entregarlo por los canales prometidos.';
    }

    public function why(): string
    {
        return 'El PDF es el producto. Hoy se entregaría a mano; automatizarlo elimina el paso más repetitivo y asegura que todos los documentos salgan iguales.';
    }

    public function priority(): string
    {
        return 'alta';
    }

    public function effort(): string
    {
        return '~1 semana';
    }

    public function scope(): array
    {
        return [
            'Maquetación del PDF con encabezado, pie, numeración y marca de agua de borrador.',
            '**Pie de trazabilidad**: versión del formato con que se generó, fecha y referencia del pedido. Sin firmas ni nombres de abogados: el documento automatizado no se revisa caso por caso y el PDF no debe sugerir lo contrario.',
            'Anexo con la **guía de radicación** específica del trámite.',
            'Entrega automática por correo al aprobar, con el PDF adjunto.',
            'Almacenamiento con URL firmada y temporal, para que el enlace no quede público.',
            'Versión en Word para los documentos que el cliente debe poder editar (contratos).',
            'Reenvío del documento desde el panel, si el cliente lo pierde.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Campo `delivered_file_path` en los pedidos.',
            'Plantillas de correo profesionales para el cliente.',
            'Acción «Marcar como entregado» en el panel.',
        ];
    }

    public function dependencies(): array
    {
        return ['El motor de plantillas, para tener qué convertir a PDF.'];
    }
}
