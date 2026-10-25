<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * ⚠️ DATOS DE DEMOSTRACIÓN.
 *
 * Estos testimonios son ficticios y existen sólo para que el prototipo se vea
 * completo. Publicar testimonios inventados es publicidad engañosa y la SIC
 * puede sancionarlo (Estatuto del Consumidor, Ley 1480 de 2011, art. 30).
 * Antes de salir a producción hay que reemplazarlos por testimonios reales
 * con autorización escrita de cada cliente, o borrar la sección entera.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $bySlug = LegalDocument::query()->pluck('id', 'slug');

        $testimonials = [
            [
                'author_name' => 'Claudia P.',
                'author_city' => 'Bogotá',
                'author_role' => 'Madre de paciente',
                'quote' => 'La EPS llevaba cuatro meses dando vueltas con la cirugía de mi hijo. Pedí la tutela un martes, la radiqué el jueves y el juez ordenó la medida provisional el lunes siguiente. La autorización salió en seis días.',
                'outcome' => 'Cirugía autorizada en 6 días',
                'rating' => 5,
                'document' => 'accion-de-tutela-salud',
                'show_on_home' => true,
                'sort_order' => 1,
            ],
            [
                'author_name' => 'Jorge A.',
                'author_city' => 'Medellín',
                'author_role' => 'Independiente',
                'quote' => 'Tenía un reporte de 2019 que ya estaba pagado desde 2021 y seguía ahí. Me explicaron que había caducado y redactaron el derecho de petición. A las dos semanas me lo quitaron.',
                'outcome' => 'Reporte eliminado en 2 semanas',
                'rating' => 5,
                'document' => 'eliminar-reporte-datacredito',
                'show_on_home' => true,
                'sort_order' => 2,
            ],
            [
                'author_name' => 'Marcela R.',
                'author_city' => 'Cali',
                'author_role' => 'Diseñadora',
                'quote' => 'Me liquidaron y la cifra no me cuadraba. El cálculo mostró que me debían las horas extras de catorce meses. Con la carta de reclamación la empresa pagó la diferencia sin llegar a demanda.',
                'outcome' => 'Recuperó $4.300.000',
                'rating' => 5,
                'document' => 'revision-de-liquidacion-laboral',
                'show_on_home' => true,
                'sort_order' => 3,
            ],
            [
                'author_name' => 'Daniel M.',
                'author_city' => 'Bogotá',
                'author_role' => 'Arrendador',
                'quote' => 'Arrendaba con una plantilla que bajé de internet. Me explicaron que tenía tres cláusulas que la ley prohíbe, incluido el depósito. El contrato nuevo ya lo usé con dos inquilinos.',
                'outcome' => 'Contrato blindado bajo Ley 820',
                'rating' => 5,
                'document' => 'contrato-de-arrendamiento-vivienda-urbana',
                'show_on_home' => true,
                'sort_order' => 4,
            ],
            [
                'author_name' => 'Sebastián V.',
                'author_city' => 'Barranquilla',
                'author_role' => 'Desarrollador',
                'quote' => 'Un cliente me debía dos hitos y el proyecto seguía creciendo. El contrato nuevo fijó entregables, límite de revisiones y cesión de derechos solo contra pago total. Cambió completamente la conversación.',
                'outcome' => 'Cobró los 2 hitos pendientes',
                'rating' => 5,
                'document' => 'contrato-de-prestacion-de-servicios',
                'show_on_home' => true,
                'sort_order' => 5,
            ],
            [
                'author_name' => 'Luisa F.',
                'author_city' => 'Bucaramanga',
                'author_role' => 'Conductora',
                'quote' => 'Me apareció una fotomulta de hacía dos años y medio que nunca me notificaron. El derecho de petición pedía el soporte de la notificación. No lo tenían y archivaron el comparendo.',
                'outcome' => 'Comparendo archivado',
                'rating' => 5,
                'document' => 'derecho-de-peticion-fotomultas',
                'show_on_home' => true,
                'sort_order' => 6,
            ],
            [
                'author_name' => 'Camilo E.',
                'author_city' => 'Bogotá',
                'author_role' => 'Fundador',
                'quote' => 'Habíamos armado la SAS con un modelo copiado y el banco nos rechazó la cuenta porque el objeto social no cubría la actividad. Rehicieron los estatutos y quedó resuelto.',
                'outcome' => 'SAS constituida y cuenta abierta',
                'rating' => 5,
                'document' => 'constitucion-de-sas',
                'show_on_home' => true,
                'sort_order' => 7,
            ],
            [
                'author_name' => 'Patricia G.',
                'author_city' => 'Pereira',
                'author_role' => 'Pensionada',
                'quote' => 'La aseguradora me negó el siniestro con un párrafo que no explicaba nada. El derecho de petición les exigió la póliza y el peritaje. Terminaron reconociendo la indemnización.',
                'outcome' => 'Siniestro reconocido',
                'rating' => 5,
                'document' => 'derecho-de-peticion',
                'show_on_home' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            $slug = $testimonial['document'];
            unset($testimonial['document']);

            Testimonial::query()->updateOrCreate(
                ['author_name' => $testimonial['author_name'], 'quote' => $testimonial['quote']],
                [
                    ...$testimonial,
                    'legal_document_id' => $bySlug[$slug] ?? null,
                    'is_published' => true,
                    'source' => 'whatsapp',
                ],
            );
        }
    }
}
