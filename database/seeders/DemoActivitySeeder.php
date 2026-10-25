<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Models\ServicePlan;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Enums\CaseUrgency;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Orders\Enums\PaymentMethod;
use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Services\ReferenceGenerator;
use Illuminate\Database\Seeder;

/**
 * ⚠️ DATOS DE DEMOSTRACIÓN.
 *
 * Pedidos y casos ficticios para que el panel y las gráficas no se vean
 * vacíos durante la demo. Antes de producción: `php artisan db:wipe` y
 * sembrar sólo catálogo, planes, abogados, FAQ y blog.
 */
class DemoActivitySeeder extends Seeder
{
    public function run(): void
    {
        $references = app(ReferenceGenerator::class);
        $documents = LegalDocument::query()->get()->keyBy('slug');
        $documentPlan = ServicePlan::query()->where('key', 'document')->first();
        $advisoryPlan = ServicePlan::query()->where('key', 'advisory')->first();
        $lawyers = Lawyer::query()->get()->keyBy('email');

        if ($documents->isEmpty() || ! $documentPlan) {
            return;
        }

        // ------------------------------------------------ Pedidos
        $orders = [
            ['accion-de-tutela-salud', 'Gloria Herrera Peña', 'gloria.herrera@example.com', OrderStatus::Delivered, DeliverySpeed::Express, 11],
            ['eliminar-reporte-datacredito', 'Mauricio Linares', 'mauricio.linares@example.com', OrderStatus::Delivered, DeliverySpeed::Standard, 9],
            ['contrato-de-arrendamiento-vivienda-urbana', 'Sandra Milena Ruiz', 'sandra.ruiz@example.com', OrderStatus::Delivered, DeliverySpeed::Standard, 7],
            ['revision-de-liquidacion-laboral', 'Héctor Pulido Daza', 'hector.pulido@example.com', OrderStatus::InReview, DeliverySpeed::Express, 1],
            ['derecho-de-peticion-fotomultas', 'Natalia Ospina', 'natalia.ospina@example.com', OrderStatus::InReview, DeliverySpeed::Standard, 1],
            ['promesa-de-compraventa-vehiculo', 'Fabián Córdoba', 'fabian.cordoba@example.com', OrderStatus::Paid, DeliverySpeed::Standard, 0],
            ['acuerdo-de-confidencialidad-nda', 'Ana María Quintero', 'ana.quintero@example.com', OrderStatus::Paid, DeliverySpeed::Express, 0],
            ['constitucion-de-sas', 'Ricardo Beltrán', 'ricardo.beltran@example.com', OrderStatus::AwaitingPayment, DeliverySpeed::Standard, 0],
            ['derecho-de-peticion', 'Yolanda Arias', 'yolanda.arias@example.com', OrderStatus::Delivered, DeliverySpeed::Standard, 14],
            ['contrato-de-prestacion-de-servicios', 'Esteban Gil Morales', 'esteban.gil@example.com', OrderStatus::Delivered, DeliverySpeed::Standard, 18],
        ];

        foreach ($orders as [$slug, $name, $email, $status, $speed, $daysAgo]) {
            $document = $documents[$slug] ?? null;

            if (! $document) {
                continue;
            }

            $plan = $slug === 'constitucion-de-sas' ? ($advisoryPlan ?? $documentPlan) : $documentPlan;
            $createdAt = now()->subDays($daysAgo)->subHours(random_int(1, 9));
            $amount = (int) ($plan->priceFor($speed) + $document->price_modifier);
            $paid = $status !== OrderStatus::AwaitingPayment;

            Order::query()->updateOrCreate(['customer_email' => $email, 'legal_document_id' => $document->id], [
                'reference' => $references->for(Order::class, 'HL'),
                'service_plan_id' => $plan->id,
                'speed' => $speed,
                'status' => $status,
                'customer_name' => $name,
                'customer_phone' => '+57 3'.random_int(10, 25).' '.random_int(100, 999).' '.random_int(1000, 9999),
                'customer_document_type' => 'CC',
                'customer_document_number' => (string) random_int(10_000_000, 1_100_000_000),
                'customer_city' => collect(['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Bucaramanga'])->random(),
                'case_summary' => 'Caso de demostración cargado por el seeder para poblar el panel.',
                'amount' => $amount,
                'payment_method' => $paid ? collect(PaymentMethod::cases())->random() : null,
                'paid_at' => $paid ? $createdAt->copy()->addMinutes(random_int(3, 60)) : null,
                'promised_at' => $createdAt->copy()->addHours($speed->hours()),
                'delivered_at' => $status === OrderStatus::Delivered ? $createdAt->copy()->addHours(random_int(2, 22)) : null,
                'assigned_lawyer_id' => $lawyers->values()->random()->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // ------------------------------------------------ Casos complejos
        $cases = [
            [
                'client_name' => 'Álvaro Sepúlveda Rincón',
                'client_email' => 'alvaro.sepulveda@example.com',
                'practice_area' => 'familia',
                'title' => 'Sucesión de mi padre con cuatro herederos y un inmueble en disputa',
                'description' => 'Mi padre falleció hace ocho meses sin testamento. Somos cuatro hermanos y uno de ellos vive en el apartamento de la sucesión y no quiere salir ni aceptar la venta. Necesitamos abrir el proceso y resolver la ocupación.',
                'urgency' => CaseUrgency::Normal,
                'status' => CaseStatus::Assigned,
                'winner' => 'daniela.restrepo@hazlolegal.co',
                'days_ago' => 6,
            ],
            [
                'client_name' => 'Inversiones Kalma S.A.S.',
                'client_email' => 'gerencia@kalma.example.com',
                'practice_area' => 'laboral',
                'title' => 'Demanda laboral de un extrabajador por despido indirecto',
                'description' => 'Un extrabajador nos demandó alegando despido indirecto y acoso laboral. Pide reintegro e indemnización. Tenemos toda la documentación del proceso disciplinario y necesitamos representación en la audiencia.',
                'urgency' => CaseUrgency::High,
                'status' => CaseStatus::Dispatched,
                'winner' => null,
                'days_ago' => 1,
            ],
            [
                'client_name' => 'Rosalba Jiménez Castro',
                'client_email' => 'rosalba.jimenez@example.com',
                'practice_area' => 'inmobiliario',
                'title' => 'Restitución de inmueble arrendado con nueve meses de mora',
                'description' => 'Mi inquilino lleva nueve meses sin pagar y no responde los requerimientos. El contrato está firmado y tengo los comprobantes de los pagos que sí hizo. Necesito iniciar el proceso de restitución.',
                'urgency' => CaseUrgency::Critical,
                'status' => CaseStatus::Assigned,
                'winner' => 'juan.paez@hazlolegal.co',
                'days_ago' => 3,
            ],
        ];

        foreach ($cases as $data) {
            $winnerEmail = $data['winner'];
            $daysAgo = $data['days_ago'];
            unset($data['winner'], $data['days_ago']);

            $createdAt = now()->subDays($daysAgo);
            $winner = $winnerEmail ? ($lawyers[$winnerEmail] ?? null) : null;

            $case = LegalCase::query()->updateOrCreate(
                ['client_email' => $data['client_email'], 'title' => $data['title']],
                [
                    ...$data,
                    'reference' => $references->for(LegalCase::class, 'CC'),
                    'client_phone' => '+57 31'.random_int(0, 9).' '.random_int(100, 999).' '.random_int(1000, 9999),
                    'client_city' => collect(['Bogotá', 'Medellín', 'Cali'])->random(),
                    'has_documents' => true,
                    'dispatched_at' => $createdAt->copy()->addMinutes(2),
                    'expires_at' => $createdAt->copy()->addHours((int) config('hazlolegal.cases.offer_ttl_hours')),
                    'assigned_lawyer_id' => $winner?->id,
                    'assigned_at' => $winner ? $createdAt->copy()->addHours(random_int(1, 20)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ],
            );

            // Una oferta por abogado del área, como lo haría el despachador real.
            $candidates = $lawyers->filter(
                fn (Lawyer $lawyer) => in_array($case->practice_area->value, $lawyer->practice_areas ?? [], true)
            );

            if ($candidates->isEmpty()) {
                $candidates = $lawyers->take(4);
            }

            foreach ($candidates as $lawyer) {
                $isWinner = $winner && $lawyer->id === $winner->id;

                CaseOffer::query()->updateOrCreate(
                    ['legal_case_id' => $case->id, 'lawyer_id' => $lawyer->id],
                    [
                        'status' => match (true) {
                            $isWinner => CaseOfferStatus::Accepted,
                            $winner !== null => CaseOfferStatus::Superseded,
                            default => CaseOfferStatus::Pending,
                        },
                        'sent_at' => $case->dispatched_at,
                        'responded_at' => $winner ? $case->assigned_at : null,
                    ],
                );
            }

            $case->update(['offers_sent' => $case->offers()->count()]);
        }
    }
}
