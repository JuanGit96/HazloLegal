<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Seeder;

/**
 * Equipo de demostración. Los datos son de prueba: antes de publicar hay que
 * reemplazarlos por abogados reales con tarjeta profesional verificable,
 * porque son la base de la señal E-E-A-T que exige Google en contenido legal.
 */
class LawyerSeeder extends Seeder
{
    public function run(): void
    {
        $lawyers = [
            [
                'name' => 'Daniela Restrepo Gómez',
                'email' => 'daniela.restrepo@hazlolegal.co',
                'phone' => '+57 301 000 0001',
                'professional_card' => '284.912',
                'university' => 'Universidad Externado de Colombia',
                'headline' => 'Especialista en derecho a la salud y seguridad social',
                'bio' => 'Doce años litigando tutelas contra EPS y entidades del sistema de salud. Ha logrado más de 600 autorizaciones de procedimientos y medicamentos negados.',
                'years_experience' => 12,
                'city' => 'Bogotá',
                'practice_areas' => ['salud', 'familia'],
                'specialties' => ['Acción de tutela', 'Derecho a la salud', 'Seguridad social', 'Incapacidades'],
                'rating' => 5.0,
                'cases_resolved' => 612,
                'is_author' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Andrés Camilo Vargas Ruiz',
                'email' => 'andres.vargas@hazlolegal.co',
                'phone' => '+57 301 000 0002',
                'professional_card' => '197.455',
                'university' => 'Universidad Nacional de Colombia',
                'headline' => 'Especialista en derecho financiero y habeas data',
                'bio' => 'Lleva una década peleando reportes negativos injustos en centrales de riesgo y cobros indebidos de bancos y aseguradoras.',
                'years_experience' => 10,
                'city' => 'Medellín',
                'practice_areas' => ['financiero', 'datos', 'consumo'],
                'specialties' => ['Habeas data', 'Datacrédito', 'Cobros indebidos', 'Protección al consumidor financiero'],
                'rating' => 4.9,
                'cases_resolved' => 438,
                'is_author' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'María Fernanda Ocampo Díaz',
                'email' => 'maria.ocampo@hazlolegal.co',
                'phone' => '+57 301 000 0003',
                'professional_card' => '233.108',
                'university' => 'Pontificia Universidad Javeriana',
                'headline' => 'Especialista en derecho laboral',
                'bio' => 'Audita liquidaciones, calcula prestaciones y litiga despidos sin justa causa. Ha recuperado más de $2.000 millones en prestaciones mal pagadas.',
                'years_experience' => 14,
                'city' => 'Bogotá',
                'practice_areas' => ['laboral'],
                'specialties' => ['Liquidaciones', 'Despido sin justa causa', 'Acoso laboral', 'Horas extras y recargos'],
                'rating' => 5.0,
                'cases_resolved' => 521,
                'is_author' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Juan Sebastián Páez Molina',
                'email' => 'juan.paez@hazlolegal.co',
                'phone' => '+57 301 000 0004',
                'professional_card' => '258.740',
                'university' => 'Universidad del Rosario',
                'headline' => 'Especialista en derecho inmobiliario y contratos',
                'bio' => 'Redacta y blinda contratos de arrendamiento, promesas de compraventa y contratos de obra. Experto en cláusulas de protección patrimonial.',
                'years_experience' => 9,
                'city' => 'Bogotá',
                'practice_areas' => ['inmobiliario', 'consumo'],
                'specialties' => ['Arrendamientos', 'Promesas de compraventa', 'Restitución de inmueble', 'Propiedad horizontal'],
                'rating' => 4.9,
                'cases_resolved' => 376,
                'is_author' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Laura Catalina Mejía Soto',
                'email' => 'laura.mejia@hazlolegal.co',
                'phone' => '+57 301 000 0005',
                'professional_card' => '271.603',
                'university' => 'Universidad de los Andes',
                'headline' => 'Especialista en derecho societario y propiedad intelectual',
                'bio' => 'Constituye sociedades, estructura acuerdos de socios y protege activos intangibles de startups y agencias.',
                'years_experience' => 8,
                'city' => 'Bogotá',
                'practice_areas' => ['empresarial', 'datos'],
                'specialties' => ['Constitución de SAS', 'Acuerdos de confidencialidad', 'Propiedad intelectual', 'Acuerdos de socios'],
                'rating' => 5.0,
                'cases_resolved' => 294,
                'is_author' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Óscar Iván Betancur Lopera',
                'email' => 'oscar.betancur@hazlolegal.co',
                'phone' => '+57 301 000 0006',
                'professional_card' => '210.887',
                'university' => 'Universidad de Antioquia',
                'headline' => 'Especialista en derecho de tránsito y sancionatorio',
                'bio' => 'Anula comparendos por indebida notificación y caducidad. Conoce el procedimiento de las secretarías de tránsito de memoria.',
                'years_experience' => 11,
                'city' => 'Medellín',
                'practice_areas' => ['transito', 'consumo'],
                'specialties' => ['Fotomultas', 'Comparendos', 'Caducidad sancionatoria', 'Recursos administrativos'],
                'rating' => 4.8,
                'cases_resolved' => 489,
                'is_author' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($lawyers as $lawyer) {
            Lawyer::query()->updateOrCreate(['email' => $lawyer['email']], $lawyer);
        }
    }
}
