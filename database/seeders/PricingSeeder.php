<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServiceAddon;
use Illuminate\Database\Seeder;

/**
 * Aplica el modelo «base + adicionales» a todo el catálogo.
 *
 * La lógica comercial detrás de cada fila:
 *
 * - El derecho de petición vale cero. Es el imán: es lo que más se busca en
 *   Google, lo que la competencia regala, y el que trae el lead que sostiene
 *   la suscripción de abogados. Se monetiza con el instructivo.
 * - La tutela no tiene autoservicio. Se gana o se pierde en la narración de
 *   los hechos; un formato automático produciría documentos que el juez
 *   niega, y eso costaría la reputación entera.
 * - En los trámites de pago el instructivo va incluido (precio cero): ya está
 *   dentro del precio y sirve para que el PDF con publicidad circule igual.
 */
class PricingSeeder extends Seeder
{
    public function run(): void
    {
        $addons = ServiceAddon::query()->get()->keyBy('key');

        // slug => [base, descuento %, autoservicio, horas, adicionales => precio]
        $pricing = [
            'derecho-de-peticion' => [
                'base' => 0, 'free' => true, 'self' => true, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'instructivo' => ['price' => 19_900, 'recommended' => true, 'preselected' => false],
                    'firma_digital' => ['price' => 19_900],
                    'express' => ['price' => 60_000],
                    'acompanamiento' => ['price' => 129_900, 'recommended' => true],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'derecho-de-peticion-fotomultas' => [
                'base' => 0, 'free' => true, 'self' => true, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'instructivo' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 60_000],
                    'acompanamiento' => ['price' => 129_900],
                ],
            ],
            'eliminar-reporte-datacredito' => [
                'base' => 0, 'free' => true, 'self' => true, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'instructivo' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 60_000],
                    'acompanamiento' => ['price' => 129_900, 'recommended' => true],
                ],
            ],
            'accion-de-tutela-salud' => [
                'base' => 0, 'free' => false, 'self' => false, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'acompanamiento' => ['price' => 280_000, 'recommended' => true, 'preselected' => true],
                    'express' => ['price' => 110_000],
                    'instructivo' => ['price' => 0],
                ],
            ],
            'acuerdo-de-confidencialidad-nda' => [
                'base' => 49_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 20,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 40_000],
                    'acompanamiento' => ['price' => 90_100],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'promesa-de-compraventa-vehiculo' => [
                'base' => 59_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 15,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 40_000],
                    'acompanamiento' => ['price' => 110_100],
                ],
            ],
            'contrato-de-arrendamiento-vivienda-urbana' => [
                'base' => 89_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 20,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 80_000],
                    'acompanamiento' => ['price' => 120_100],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'contrato-de-prestacion-de-servicios' => [
                'base' => 99_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 20,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 90_000],
                    'acompanamiento' => ['price' => 140_100],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'revision-de-liquidacion-laboral' => [
                'base' => 0, 'free' => false, 'self' => false, 'hours' => 48, 'discount' => 0,
                'addons' => [
                    'acompanamiento' => ['price' => 150_000, 'recommended' => true, 'preselected' => true],
                    'express' => ['price' => 70_000],
                    'instructivo' => ['price' => 0],
                ],
            ],
            'constitucion-de-sas' => [
                'base' => 0, 'free' => false, 'self' => false, 'hours' => 48, 'discount' => 0,
                'addons' => [
                    'acompanamiento' => ['price' => 290_000, 'recommended' => true, 'preselected' => true],
                    'express' => ['price' => 120_000],
                    'instructivo' => ['price' => 0],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            /*
            | Los cinco productos que entraron con las especificaciones del
            | equipo. Llevan el mismo juego de adicionales que sus hermanos:
            | si un trámite entrega un documento, todos los toppings que
            | tengan sentido para él se le ofrecen.
            */
            'derecho-de-peticion-pensiones' => [
                'base' => 0, 'free' => true, 'self' => true, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'instructivo' => ['price' => 19_900, 'recommended' => true],
                    'firma_digital' => ['price' => 19_900],
                    'express' => ['price' => 60_000],
                    'acompanamiento' => ['price' => 129_900, 'recommended' => true],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'reclamacion-por-garantia-legal' => [
                'base' => 29_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 15,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900],
                    'express' => ['price' => 40_000],
                    'acompanamiento' => ['price' => 90_100, 'recommended' => true],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'pqr-servicios-publicos' => [
                'base' => 0, 'free' => true, 'self' => true, 'hours' => 24, 'discount' => 0,
                'addons' => [
                    'instructivo' => ['price' => 19_900, 'recommended' => true],
                    'firma_digital' => ['price' => 19_900],
                    'express' => ['price' => 60_000],
                    'acompanamiento' => ['price' => 129_900],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'poder-especial-por-mensaje-de-datos' => [
                'base' => 39_900, 'free' => false, 'self' => true, 'hours' => 24, 'discount' => 20,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900, 'recommended' => true],
                    'express' => ['price' => 40_000],
                    'sesion' => ['price' => 79_900],
                ],
            ],
            'paquete-cumplimiento-pymes' => [
                'base' => 249_900, 'free' => false, 'self' => true, 'hours' => 48, 'discount' => 15,
                'addons' => [
                    'instructivo' => ['price' => 0, 'preselected' => true],
                    'firma_digital' => ['price' => 19_900],
                    'express' => ['price' => 150_000],
                    'acompanamiento' => ['price' => 290_000, 'recommended' => true],
                    'sesion' => ['price' => 79_900],
                ],
            ],
        ];

        foreach ($pricing as $slug => $config) {
            $document = LegalDocument::query()->where('slug', $slug)->first();

            if (! $document) {
                continue;
            }

            $document->update([
                'base_price' => $config['base'],
                'is_free' => $config['free'],
                'self_service_enabled' => $config['self'],
                'delivery_hours' => $config['hours'],
                'express_delivery_hours' => 0,
                'discount_percentage' => $config['discount'],
            ]);

            $sync = [];
            $order = 0;

            foreach ($config['addons'] as $key => $settings) {
                if (! isset($addons[$key])) {
                    continue;
                }

                $sync[$addons[$key]->id] = [
                    'price' => $settings['price'],
                    'is_available' => true,
                    'is_recommended' => $settings['recommended'] ?? false,
                    'is_preselected' => $settings['preselected'] ?? false,
                    'sort_order' => $order++,
                ];
            }

            $document->addons()->sync($sync);
        }
    }
}
