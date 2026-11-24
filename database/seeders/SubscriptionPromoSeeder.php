<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Subscriptions\Models\SubscriptionPromo;
use Illuminate\Database\Seeder;

/**
 * Las dos condiciones de entrada a la suscripción.
 *
 * El precio de lanzamiento es lo que recibe cualquier abogado que llegue por
 * su cuenta, y existe porque una red con pocos abogados todavía no vale lo
 * que valdrá: cobrar el precio de régimen desde el primer día sería cobrar
 * por una audiencia que aún no está.
 *
 * El enlace de invitación es para los abogados que se traen a mano al
 * principio, los que llenan el directorio y aceptan los primeros casos. Esos
 * meses gratis son el costo de arrancar.
 *
 * En los dos casos se registra la tarjeta antes de empezar y las fechas de
 * cobro se muestran. Lo que no se hace nunca es dejar que el precio suba sin
 * avisar: se le dice al abogado el día exacto y el valor.
 */
class SubscriptionPromoSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPromo::query()->updateOrCreate(
            ['code' => null],
            [
                'is_default' => true,
                'name' => 'Precio de lanzamiento',
                'tagline' => 'Mientras la red crece, los primeros tres meses cuestan la mitad.',
                'terms' => 'Pasados los tres primeros periodos se cobra el precio de lista del plan. '
                    .'Te avisamos por correo quince días antes y puedes cancelar cuando quieras.',
                'free_months' => 0,
                'discount_percentage' => 50,
                'discount_months' => 3,
                'lawyer_plan_id' => null,
                'is_active' => true,
            ],
        );

        // Código de ejemplo para probar el flujo de invitación de punta a
        // punta. Es dato de demostración: cámbialo o desactívalo antes de
        // repartir enlaces de verdad.
        SubscriptionPromo::query()->updateOrCreate(
            ['code' => 'FUNDADORES'],
            [
                'is_default' => false,
                'name' => 'Abogados fundadores (demostración)',
                'tagline' => 'Tres meses completos sin costo por ser de los primeros.',
                'terms' => 'Registras tu tarjeta hoy y no se cobra nada durante tres meses. '
                    .'El primer cobro es el día que se cumplen, al precio de lista del plan, '
                    .'y puedes cancelar antes sin costo.',
                'free_months' => 3,
                'discount_percentage' => 0,
                'discount_months' => 0,
                'lawyer_plan_id' => null,
                'max_redemptions' => 50,
                'is_active' => true,
            ],
        );
    }
}
