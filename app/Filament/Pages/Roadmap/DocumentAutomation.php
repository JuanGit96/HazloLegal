<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class DocumentAutomation extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Plantillas y automatización';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Plantillas y automatización de documentos';

    public function summary(): string
    {
        return 'El motor que convierte una plantilla maestra más los datos del cliente en el borrador del documento, para los trámites de Fase 1.';
    }

    public function why(): string
    {
        return 'Es lo que hace rentable el precio de $90.000. Hoy el abogado redacta cada documento; con el motor solo audita. Es el módulo que más margen libera.';
    }

    public function priority(): string
    {
        return 'alta';
    }

    public function effort(): string
    {
        return '~3 semanas';
    }

    public function scope(): array
    {
        return [
            '**Editor de plantillas maestras** por trámite, con variables tipo `{{nombre_cliente}}`, `{{numero_obligacion}}`, `{{ciudad}}`.',
            '**Catálogo de variables** declarado por trámite, con tipo (texto, número, fecha, selección) y reglas de validación, para que el formulario del cliente se genere solo.',
            '**Bloques condicionales**: párrafos que aparecen según la respuesta (por ejemplo, la causal de Datacrédito que aplique).',
            '**Versionado de plantillas**: si una ley cambia, saber qué documentos salieron con qué versión.',
            '**Previsualización** del documento ensamblado antes de que el abogado lo apruebe.',
            '**Cola de auditoría**: el abogado ve el borrador, corrige y aprueba con un clic.',
            'Biblioteca de **cláusulas reutilizables** entre plantillas.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Catálogo de trámites con la clasificación Fase 1 / Fase 2.',
            'Campo «qué información necesitamos» por trámite, que es la base del catálogo de variables.',
            'Captura del caso en el checkout, guardada en `orders.answers`.',
        ];
    }

    public function dependencies(): array
    {
        return ['Definir las plantillas maestras con el equipo jurídico, trámite por trámite.'];
    }
}
