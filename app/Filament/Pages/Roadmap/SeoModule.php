<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class SeoModule extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'Herramientas de SEO';

    protected static ?int $navigationSort = 11;

    protected static ?string $title = 'Herramientas de SEO y contenido';

    public function summary(): string
    {
        return 'Pasar de «publicamos contenido» a «sabemos qué contenido publicar y cómo va el que ya publicamos».';
    }

    public function why(): string
    {
        return 'El blog es el canal de adquisición más barato del negocio, pero solo si se trabaja con datos. Esto convierte la intuición sobre qué escribir en una lista priorizada.';
    }

    public function priority(): string
    {
        return 'media';
    }

    public function effort(): string
    {
        return '~2 semanas';
    }

    public function scope(): array
    {
        return [
            'Conexión con **Google Search Console** para traer impresiones, clics y posición de cada página.',
            'Lista de **palabras clave objetivo** con su posición actual y su evolución.',
            'Detector de **huecos de contenido**: búsquedas donde aparecemos en posición 5-20 y un artículo mejor nos subiría.',
            'Alerta de **canibalización**: dos páginas compitiendo por la misma búsqueda.',
            'Auditoría automática por página: falta título SEO, meta descripción muy corta, sin enlaces internos, contenido muy breve.',
            'Sugerencias de enlazado interno entre artículos y trámites del mismo cluster.',
            'Calendario editorial con el responsable y el abogado revisor de cada pieza.',
            'Aviso cuando un artículo lleva más de seis meses sin revisión jurídica (la frescura pesa en contenido legal).',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Campos de título SEO, meta descripción, palabra clave y palabras secundarias en trámites y artículos.',
            'Sitemap dinámico y robots.txt.',
            'Datos estructurados: LegalService, Service, Offer, FAQPage, BreadcrumbList y BlogPosting con autor acreditado.',
            'Pestaña «Sin SEO completo» en el listado de artículos.',
        ];
    }

    public function dependencies(): array
    {
        return ['Propiedad verificada en Google Search Console.'];
    }
}
