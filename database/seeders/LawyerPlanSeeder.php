<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Subscriptions\Models\LawyerPlan;
use Illuminate\Database\Seeder;

/**
 * Los planes de suscripción de abogados: la segunda fuente de ingresos y la
 * que escala sin costo marginal.
 *
 * La propuesta no es «aparece en un directorio»: es «te ponemos delante de
 * gente que acaba de describir su problema por escrito». Por eso el plan
 * alto incluye acceso a los leads que autorizaron ser contactados.
 */
class LawyerPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'directorio',
                'name' => 'Directorio',
                'tagline' => 'Que te encuentren.',
                'description' => 'Tu perfil público en el directorio, con tus áreas y tu ciudad.',
                'price_monthly' => 50_000,
                'price_annual' => 500_000,
                'free_trial_months' => 2,
                'features' => [
                    'Página de perfil propia, indexable en Google',
                    'Apareces en las búsquedas por especialidad y ciudad',
                    'Avisos por correo de casos de tu área',
                    'Botón de contacto directo (WhatsApp y correo)',
                    'Sin comisión sobre tus honorarios',
                ],
                'includes_profile_page' => true,
                'includes_case_alerts' => true,
                'includes_pdf_placement' => false,
                'includes_lead_data' => false,
                'includes_featured' => false,
                'includes_blog_placement' => false,
                'directory_priority' => 10,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'crecimiento',
                'name' => 'Crecimiento',
                'tagline' => 'Que te encuentren y te escriban.',
                'description' => 'Todo lo del Directorio, más presencia en los instructivos que circulan y acceso a los leads que autorizaron contacto.',
                'price_monthly' => 150_000,
                'price_annual' => 1_500_000,
                'free_trial_months' => 2,
                'features' => [
                    'Todo lo del plan Directorio',
                    'Tu firma en los instructivos PDF de tu área',
                    'Acceso a los leads que autorizaron ser contactados',
                    'Apareces al final de los artículos de tu especialidad',
                    'Prioridad alta en el orden del directorio',
                    'Panel con estadísticas de tu perfil',
                ],
                'includes_profile_page' => true,
                'includes_case_alerts' => true,
                'includes_pdf_placement' => true,
                'includes_lead_data' => true,
                'includes_featured' => false,
                'includes_blog_placement' => true,
                'directory_priority' => 50,
                'badge' => 'MÁS ELEGIDO',
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'destacado',
                'name' => 'Destacado',
                'tagline' => 'Que te vean primero.',
                'description' => 'Todo lo anterior, más posición destacada en tu ciudad y especialidad.',
                'price_monthly' => 350_000,
                'price_annual' => 3_500_000,
                'free_trial_months' => 0,
                'features' => [
                    'Todo lo del plan Crecimiento',
                    'Posición destacada en tu ciudad y especialidad',
                    'Tu firma primero en los instructivos de tu área',
                    'Perfil ampliado con galería y casos de éxito',
                    'Primera opción en el reparto de casos complejos',
                    'Informe mensual de desempeño',
                ],
                'includes_profile_page' => true,
                'includes_case_alerts' => true,
                'includes_pdf_placement' => true,
                'includes_lead_data' => true,
                'includes_featured' => true,
                'includes_blog_placement' => true,
                'directory_priority' => 100,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            LawyerPlan::query()->updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
