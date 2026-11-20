<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Tools\Models\Calculator;
use Illuminate\Database\Seeder;

/**
 * Las calculadoras son el otro imán, con una ventaja sobre el documento
 * gratuito: quien calcula su liquidación tiene un problema laboral ahora
 * mismo y un número en la cabeza. Es el lead más caliente del embudo.
 */
class CalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $docs = LegalDocument::query()->pluck('id', 'slug');

        $calculators = [
            [
                'key' => 'liquidacion',
                'name' => 'Calculadora de liquidación laboral',
                'slug' => 'calculadora-liquidacion-laboral',
                'emoji' => '💼',
                'intro' => 'Calcula cesantías, intereses, prima y vacaciones según el Código Sustantivo del Trabajo.',
                'seo_title' => 'Calculadora de liquidación laboral 2026 | Gratis',
                'seo_description' => 'Calcula tu liquidación laboral en Colombia: cesantías, intereses, prima y vacaciones. Gratis, con las fórmulas del Código Sustantivo del Trabajo.',
                'focus_keyword' => 'calculadora liquidacion laboral',
                'related_document_id' => $docs['revision-de-liquidacion-laboral'] ?? null,
                'config' => ['minimum_wage' => 1_623_500, 'transport_allowance' => 200_000, 'year' => 2026],
                'sort_order' => 1,
            ],
            [
                'key' => 'indemnizacion',
                'name' => 'Calculadora de indemnización por despido',
                'slug' => 'calculadora-indemnizacion-despido',
                'emoji' => '⚖️',
                'intro' => 'Cuánto te corresponde si te despiden sin justa causa, según tu contrato y antigüedad.',
                'seo_title' => 'Calculadora de indemnización por despido sin justa causa | Colombia',
                'seo_description' => 'Calcula la indemnización que te corresponde por despido sin justa causa en Colombia. Artículo 64 del Código Sustantivo del Trabajo.',
                'focus_keyword' => 'indemnizacion por despido sin justa causa',
                'related_document_id' => $docs['revision-de-liquidacion-laboral'] ?? null,
                'config' => ['minimum_wage' => 1_623_500, 'year' => 2026],
                'sort_order' => 2,
            ],
            [
                'key' => 'horas_extras',
                'name' => 'Calculadora de horas extras y recargos',
                'slug' => 'calculadora-horas-extras',
                'emoji' => '🕒',
                'intro' => 'Horas extras diurnas y nocturnas, recargo nocturno, dominicales y festivos.',
                'seo_title' => 'Calculadora de horas extras y recargos 2026 | Colombia',
                'seo_description' => 'Calcula cuánto te deben por horas extras, recargo nocturno, dominicales y festivos en Colombia.',
                'focus_keyword' => 'calculadora horas extras colombia',
                'related_document_id' => $docs['revision-de-liquidacion-laboral'] ?? null,
                'config' => ['monthly_hours' => 240, 'year' => 2026],
                'sort_order' => 3,
            ],
            [
                'key' => 'intereses_mora',
                'name' => 'Calculadora de intereses de mora',
                'slug' => 'calculadora-intereses-mora',
                'emoji' => '📈',
                'intro' => 'Cuánto puede cobrarte legalmente un acreedor por una deuda vencida.',
                'seo_title' => 'Calculadora de intereses de mora en Colombia | Tasa máxima legal',
                'seo_description' => 'Calcula los intereses de mora de una deuda en Colombia y comprueba si te están cobrando por encima de la tasa máxima legal.',
                'focus_keyword' => 'calculadora intereses de mora',
                'related_document_id' => $docs['eliminar-reporte-datacredito'] ?? null,
                'config' => ['max_annual_rate' => 25.5, 'year' => 2026],
                'sort_order' => 4,
            ],
        ];

        foreach ($calculators as $calculator) {
            Calculator::query()->updateOrCreate(['key' => $calculator['key']], $calculator);
        }
    }
}
