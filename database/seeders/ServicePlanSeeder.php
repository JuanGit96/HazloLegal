<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\ServicePlan;
use Illuminate\Database\Seeder;

class ServicePlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'document',
                'name' => 'Documento listo para usar',
                'tagline' => 'Tú cuentas el caso, un abogado lo redacta y lo audita.',
                'description' => 'La opción de la mayoría. Recibes el documento terminado, en PDF, con la guía de radicación.',
                'price' => 90_000,
                'express_price' => 140_000,
                'delivery_hours' => 24,
                'express_delivery_hours' => 4,
                'includes' => [
                    'Redacción y **auditoría de un abogado titulado**',
                    'Entrega en **24 horas hábiles**',
                    'PDF listo para firmar y radicar',
                    'Guía paso a paso de dónde presentarlo',
                    'Envío por correo y WhatsApp',
                    'Una ronda de ajustes incluida',
                ],
                'express_includes' => [
                    'Todo lo del plan estándar',
                    '**Entrega en menos de 4 horas**',
                    'Tu trámite salta al inicio de la fila',
                    'Auditoría prioritaria del abogado',
                    'Soporte directo por WhatsApp mientras se redacta',
                ],
                'is_quote_only' => false,
                'is_highlighted' => true,
                'badge' => 'MÁS PEDIDO',
                'cta_label' => 'Elegir mi trámite',
                'sort_order' => 1,
            ],
            [
                'key' => 'advisory',
                'name' => 'Documento + asesoría',
                'tagline' => 'Hablas con el abogado antes de que redacte.',
                'description' => 'Para cuando no tienes claro qué pedir, o hay más de un camino posible.',
                'price' => 250_000,
                'express_price' => null,
                'delivery_hours' => 48,
                'express_delivery_hours' => null,
                'includes' => [
                    '**Videollamada de 30 minutos** con un abogado',
                    'Evaluamos tu caso en vivo y definimos la estrategia',
                    'Redacción después de la sesión, con lo acordado',
                    'Resolución de todas tus dudas',
                    'Dos rondas de ajustes incluidas',
                    'Grabación y resumen escrito de la sesión',
                ],
                'is_quote_only' => false,
                'is_highlighted' => false,
                'badge' => null,
                'cta_label' => 'Agendar mi asesoría',
                'sort_order' => 2,
            ],
            [
                'key' => 'complex',
                'name' => 'Caso complejo',
                'tagline' => 'Cuando necesitas que un abogado te represente.',
                'description' => 'Demandas, litigios, sucesiones, embargos y conflictos laborales avanzados.',
                'price' => null,
                'express_price' => null,
                'delivery_hours' => null,
                'includes' => [
                    'Demandas y litigios',
                    'Sucesiones, embargos y procesos ejecutivos',
                    'Conflictos laborales avanzados',
                    '**Representación uno a uno** con abogado asignado',
                    'Cotización sin costo y sin compromiso',
                    'Un abogado toma tu caso en menos de 48 horas',
                ],
                'is_quote_only' => true,
                'is_highlighted' => false,
                'badge' => null,
                'cta_label' => 'Hablar con un abogado',
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            ServicePlan::query()->updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
