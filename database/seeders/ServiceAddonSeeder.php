<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\ServiceAddon;
use Illuminate\Database\Seeder;

class ServiceAddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            [
                'key' => 'instructivo',
                'name' => 'Instructivo paso a paso',
                'short_name' => 'Instructivo',
                'emoji' => '📘',
                'kind' => 'guidance',
                'tagline' => 'Te decimos dónde radicarlo, qué anexar y qué plazo tiene la entidad para responderte.',
                'description' => 'Un PDF con los pasos exactos para presentar tu documento sin que te lo devuelvan.',
                'bullets' => [
                    'Dónde se radica, con la dependencia exacta',
                    'Qué documentos anexar',
                    'Qué plazo legal tiene la entidad',
                    'Qué hacer si no responden a tiempo',
                ],
                'badge' => 'MÁS AGREGADO',
                'social_proof' => '8 de cada 10 lo agregan',
                'default_price' => 19_900,
                'sort_order' => 1,
            ],
            [
                'key' => 'firma_digital',
                'name' => 'Firma digital con QR',
                'short_name' => 'Firma digital',
                'emoji' => '✍️',
                'kind' => 'signature',
                'tagline' => 'Escaneas un QR, firmas con el dedo en el celular y la firma queda en el documento.',
                'description' => 'Firma electrónica con validez probatoria según la Ley 527 de 1999.',
                'bullets' => [
                    'Firmas desde el celular, sin imprimir',
                    'Validez legal (Ley 527 de 1999)',
                    'Certificado de auditoría con fecha, hora e IP',
                    'Varias partes pueden firmar el mismo documento',
                ],
                'default_price' => 19_900,
                'sort_order' => 2,
            ],
            [
                'key' => 'express',
                'name' => 'Entrega express',
                'short_name' => 'Express',
                'emoji' => '⚡',
                'kind' => 'delivery',
                'tagline' => 'Lo recibes de una, no en 24 horas.',
                'description' => 'Tu trámite salta al inicio de la fila y se entrega de inmediato.',
                'bullets' => [
                    'Entrega inmediata, sin esperar',
                    'Tu trámite va primero en la fila',
                    'Útil cuando tienes un término encima',
                ],
                'badge' => 'SI TIENES AFÁN',
                'default_price' => 60_000,
                'sort_order' => 3,
            ],
            [
                'key' => 'acompanamiento',
                'name' => 'Lo escribe un abogado',
                'short_name' => 'Con abogado',
                'emoji' => '⚖️',
                'kind' => 'review',
                'tagline' => 'Un abogado lee tu caso y redacta el documento para ti, no sobre un formato.',
                'description' => 'Redacción a la medida de tus hechos, con los argumentos que tu caso necesita.',
                'bullets' => [
                    'Un abogado lee tu caso completo',
                    'Redacción a la medida, no sobre plantilla',
                    'Jurisprudencia aplicable a tu situación',
                    'Dos rondas de ajustes incluidas',
                    'Sesión de 30 minutos incluida',
                ],
                'badge' => 'MÁXIMA SEGURIDAD',
                'default_price' => 129_900,
                'sort_order' => 4,
            ],
            [
                'key' => 'sesion',
                'name' => 'Sesión con abogado (30 min)',
                'short_name' => 'Sesión',
                'emoji' => '💬',
                'kind' => 'session',
                'tagline' => 'Resuelves tus dudas por videollamada antes de radicar.',
                'description' => 'Media hora con un abogado del área para revisar tu caso.',
                'bullets' => [
                    'Videollamada de 30 minutos',
                    'Con un abogado de tu área',
                    'Grabación y resumen escrito',
                ],
                'default_price' => 79_900,
                'sort_order' => 5,
            ],
        ];

        foreach ($addons as $addon) {
            ServiceAddon::query()->updateOrCreate(['key' => $addon['key']], $addon);
        }
    }
}
