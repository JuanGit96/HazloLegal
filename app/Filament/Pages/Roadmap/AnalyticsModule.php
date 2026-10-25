<?php

declare(strict_types=1);

namespace App\Filament\Pages\Roadmap;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class AnalyticsModule extends RoadmapPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Analítica y embudo';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Analítica, embudo y estadísticas';

    public function summary(): string
    {
        return 'Saber dónde se cae la gente antes de pagar, qué trámite deja más margen y qué canal trae clientes que vuelven.';
    }

    public function why(): string
    {
        return 'El panel ya muestra qué pasó. Esto responde por qué: si el 70 % abandona en la pantalla de pago, el problema es el pago, no el tráfico. Sin esto, cada decisión de marketing es una apuesta.';
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
            '**Embudo paso a paso**: visitas a la landing → clic en pedir → formulario iniciado → formulario completado → pago iniciado → pago confirmado, con el porcentaje de caída en cada paso.',
            '**Rentabilidad por trámite**: ingreso, horas de abogado invertidas y margen real. Permite decidir qué trámite empujar y cuál subir de precio.',
            '**Atribución por canal** usando los UTM que ya guardamos: qué campaña trae pedidos, no solo visitas.',
            'Costo de adquisición por cliente y valor de vida, para saber cuánto se puede pagar por un clic.',
            '**Panel de SEO**: posición de cada palabra clave objetivo, páginas que ganan y pierden tráfico, trámites sin contenido largo.',
            'Tiempo real de entrega frente al prometido, por trámite y por abogado.',
            'Cohortes de clientes que vuelven a comprar.',
            'Informe mensual automático al correo de los socios.',
        ];
    }

    public function alreadyInPlace(): array
    {
        return [
            'Campos UTM capturados en pedidos y casos.',
            'Contadores de visitas y pedidos por trámite.',
            'Gráficas de tendencia de pedidos, ingresos por trámite y respuesta de abogados en el panel.',
        ];
    }

    public function dependencies(): array
    {
        return [
            'Eventos de front-end para medir los pasos intermedios del embudo.',
            'Google Search Console conectado, para los datos de posición.',
        ];
    }
}
