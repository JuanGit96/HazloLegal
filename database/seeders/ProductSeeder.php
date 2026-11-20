<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Catalog\Models\DocumentClause;
use App\Domain\Catalog\Models\DocumentRiskRule;
use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Database\Seeder;

/**
 * Los nueve productos, tal como los especificó el equipo.
 *
 * Cada archivo de `data/productos/` trae lo que define a un trámite: el
 * formulario propio, el documento que se entrega —casillas de petición o
 * plantilla, según el caso— y el instructivo, que es lo que se cobra.
 *
 * La redacción jurídica de todo esto es un primer borrador: las
 * especificaciones marcan con [MAICOL] lo que un abogado tiene que aprobar
 * antes de publicarlo. Por eso las casillas entran con
 * `needs_lawyer_review` en true.
 */
class ProductSeeder extends Seeder
{
    /**
     * archivo => datos del trámite.
     *
     * Los que ya existen en el catálogo se actualizan; los cuatro que no,
     * se crean aquí.
     */
    private const PRODUCTOS = [
        '01-derecho-de-peticion' => [
            'slug' => 'derecho-de-peticion',
        ],
        '02-derecho-de-peticion-pensiones' => [
            'slug' => 'derecho-de-peticion-pensiones',
            'crear' => [
                'name' => 'Derecho de petición a fondo de pensiones',
                'short_name' => 'Petición a pensiones',
                'practice_area' => 'laboral',
                'automation_phase' => 'automated',
                'emoji' => '👵',
                'tagline' => 'Pide tu historia laboral o el estado de tu pensión, y obliga a que te respondan.',
                'summary' => 'Derecho de petición dirigido a Colpensiones o a tu fondo privado para pedir historia laboral, certificación de semanas, estado del trámite de pensión o pago de mesadas.',
                'legal_basis' => 'Constitución Política (art. 23) y Ley 1755 de 2015',
                'base_price' => 0,
                'is_free' => true,
                'self_service_enabled' => true,
                'supports_signature' => true,
            ],
        ],
        '03-arrendamiento-vivienda' => [
            'slug' => 'contrato-de-arrendamiento-vivienda-urbana',
        ],
        '04-prestacion-de-servicios' => [
            'slug' => 'contrato-de-prestacion-de-servicios',
        ],
        '05-acuerdo-de-confidencialidad' => [
            'slug' => 'acuerdo-de-confidencialidad-nda',
        ],
        '06-poder-especial' => [
            'slug' => 'poder-especial-por-mensaje-de-datos',
            'crear' => [
                'name' => 'Poder especial por mensaje de datos',
                'short_name' => 'Poder especial',
                'practice_area' => 'familia',
                'automation_phase' => 'automated',
                'emoji' => '✍️',
                'tagline' => 'Autoriza a tu abogado sin ir a la notaría.',
                'summary' => 'Poder especial que se envía por correo electrónico desde tu propia cuenta. Los juzgados lo aceptan sin autenticación, lo que ahorra la ida a la notaría.',
                'legal_basis' => 'Ley 527 de 1999 y Código General del Proceso',
                'base_price' => 39_900,
                'self_service_enabled' => true,
                'supports_signature' => true,
            ],
        ],
        '07-reclamacion-garantia' => [
            'slug' => 'reclamacion-por-garantia-legal',
            'crear' => [
                'name' => 'Reclamación por garantía legal',
                'short_name' => 'Reclamo de garantía',
                'practice_area' => 'consumo',
                'automation_phase' => 'automated',
                'emoji' => '🛍️',
                'tagline' => 'Reclama al vendedor antes de poder demandar ante la SIC.',
                'summary' => 'Reclamación directa al proveedor o al productor por un producto defectuoso, un servicio mal prestado o una compra en línea que no llegó. Es el paso previo obligado antes de acudir a la Superintendencia.',
                'legal_basis' => 'Ley 1480 de 2011, Estatuto del Consumidor',
                'base_price' => 29_900,
                'self_service_enabled' => true,
                'supports_signature' => true,
            ],
        ],
        '08-pqr-servicios-publicos' => [
            'slug' => 'pqr-servicios-publicos',
            'crear' => [
                'name' => 'Reclamación de servicios públicos',
                'short_name' => 'Reclamo de servicios',
                'practice_area' => 'consumo',
                'automation_phase' => 'automated',
                'emoji' => '💡',
                'tagline' => 'Si no te responden en 15 días hábiles, la ley entiende que te dieron la razón.',
                'summary' => 'Reclamación ante la empresa de energía, gas, acueducto o aseo por un cobro que no corresponde, una suspensión o fallas en el servicio. Incluye cómo exigir el silencio administrativo positivo.',
                'legal_basis' => 'Ley 142 de 1994, régimen de los servicios públicos domiciliarios',
                'base_price' => 0,
                'is_free' => true,
                'self_service_enabled' => true,
                'supports_signature' => true,
            ],
        ],
        '09-cumplimiento-pymes' => [
            'slug' => 'paquete-cumplimiento-pymes',
            'crear' => [
                'name' => 'Paquete de cumplimiento de datos para pymes',
                'short_name' => 'Cumplimiento pymes',
                'practice_area' => 'datos',
                'automation_phase' => 'automated',
                'emoji' => '🛡️',
                'tagline' => 'Política de datos, aviso de privacidad, autorizaciones y términos del sitio.',
                'summary' => 'Los cuatro documentos que la Ley 1581 de 2012 le exige a cualquier empresa que recoja datos, coherentes entre sí y listos para publicar.',
                'legal_basis' => 'Ley 1581 de 2012 y Decreto 1377 de 2013',
                'base_price' => 249_900,
                'self_service_enabled' => true,
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTOS as $archivo => $config) {
            $spec = require database_path("seeders/data/productos/{$archivo}.php");

            $document = $this->document($config);

            $this->metadata($document, $spec);
            $this->clauses($document, $spec['clauses'] ?? []);
            $this->riskRules($document, $spec['risk'] ?? []);
        }
    }

    private function document(array $config): LegalDocument
    {
        $existente = LegalDocument::query()->where('slug', $config['slug'])->first();

        if ($existente) {
            return $existente;
        }

        $nuevo = $config['crear'] ?? null;

        if (! $nuevo) {
            throw new \RuntimeException("El trámite {$config['slug']} no existe y no se indicó cómo crearlo.");
        }

        return LegalDocument::query()->create([
            'slug' => $config['slug'],
            'is_active' => true,
            'practice_area' => PracticeArea::from($nuevo['practice_area']),
            'delivery_hours' => 24,
            'express_delivery_hours' => 0,
            ...$nuevo,
        ]);
    }

    /** El formulario, la plantilla y todo lo que alimenta el instructivo. */
    private function metadata(LegalDocument $document, array $spec): void
    {
        $document->forceFill(array_filter([
            'template_heading' => $spec['template_heading'] ?? null,
            'template_body' => $spec['template_body'] ?? null,
            'template_fields' => $spec['fields'] ?? null,
            'clause_common_basis' => $spec['common_basis'] ?? null,
            'clause_closing' => $spec['closing'] ?? null,
            'filing_entity' => $spec['filing_entity'] ?? null,
            'filing_steps' => $spec['filing_steps'] ?? null,
            'response_deadline_days' => $spec['response_deadline_days'] ?? null,
            'instructions_intro' => $spec['instructions_intro'] ?? null,
            'instructions_footer' => $spec['instructions_footer'] ?? null,
            'instructions_attachments' => $spec['instructions_attachments'] ?? null,
            'instructions_warnings' => $spec['instructions_warnings'] ?? null,
            'glossary' => $spec['glossary'] ?? null,
        ], fn ($v) => $v !== null))->saveQuietly();
    }

    private function clauses(LegalDocument $document, array $clauses): void
    {
        foreach ($clauses as $i => $clause) {
            DocumentClause::query()->updateOrCreate(
                ['legal_document_id' => $document->id, 'key' => $clause['key']],
                [
                    'label' => $clause['label'],
                    'body' => $clause['body'] ?? null,
                    'legal_basis' => $clause['legal_basis'] ?? null,
                    'variables' => $clause['variables'] ?? null,
                    'is_free_text' => $clause['is_free_text'] ?? false,
                    'max_chars' => $clause['max_chars'] ?? null,
                    'response_deadline_days' => $clause['response_deadline_days'] ?? null,
                    // La redacción es un borrador hasta que un abogado la
                    // apruebe: así lo exigen las especificaciones.
                    'needs_lawyer_review' => $clause['needs_lawyer_review'] ?? true,
                    'is_active' => true,
                    'sort_order' => ($i + 1) * 10,
                ],
            );
        }
    }

    private function riskRules(LegalDocument $document, array $reglas): void
    {
        $document->riskRules()->delete();

        foreach ($reglas as $i => $regla) {
            DocumentRiskRule::query()->create([
                'legal_document_id' => $document->id,
                'kind' => $regla['kind'],
                'level' => $regla['level'],
                'key' => $regla['key'] ?? null,
                'prompt' => $regla['prompt'],
                'escalates_with_others' => $regla['escalates_with_others'] ?? false,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
            ]);
        }
    }
}
