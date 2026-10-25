<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class InvoicingModule extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Facturación electrónica';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Facturación electrónica (DIAN)';

    public function summary(): string
    {
        return 'Emitir la factura electrónica de cada venta conforme a la normativa de la DIAN, y manejar las notas de crédito de las devoluciones.';
    }

    public function why(): string
    {
        return 'Es una obligación legal desde la primera venta, no una mejora opcional. Además, sin factura electrónica no se le puede vender a empresas, que son los clientes de mayor ticket (NDA, SAS, contratos).';
    }

    public function priority(): string
    {
        return 'media';
    }

    public function effort(): string
    {
        return '~1,5 semanas con proveedor';
    }

    public function scope(): array
    {
        return [
            'Integración con un **proveedor tecnológico autorizado** por la DIAN (Alegra, Siigo, Factus o similar).',
            'Emisión automática al confirmar el pago, con el CUFE y el XML.',
            'Envío de la factura al cliente junto con la confirmación.',
            '**Notas de crédito** cuando procede una devolución por garantía.',
            'Datos de facturación en el checkout para clientes empresa (razón social, NIT, responsabilidad tributaria).',
            'Resolución de numeración y rango autorizado configurables.',
            'Reporte mensual para el contador.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Pedidos con valor, medio de pago y fecha de pago.',
            'Campo de tipo y número de documento del cliente, incluido NIT.',
        ];
    }

    public function dependencies(): array
    {
        return [
            'La sociedad constituida con RUT y responsabilidad de facturar.',
            'Resolución de facturación de la DIAN.',
            'Pasarela de pagos, para emitir en el momento del cobro.',
        ];
    }
}
