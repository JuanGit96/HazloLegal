<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Services;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\LegalCases\Data\AssignmentResult;
use App\Domain\LegalCases\Enums\AssignmentOutcome;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Mail\CaseShortlistMail;
use App\Domain\LegalCases\Mail\CaseTakenByOtherMail;
use App\Domain\LegalCases\Mail\CaseWonMail;
use App\Domain\LegalCases\Mail\LawyerAssignedMail;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * El reparto de un caso, en dos tiempos.
 *
 * Antes ganaba el primero que aceptaba. Premiaba la velocidad de respuesta
 * del abogado, no lo que al cliente le conviene: quien tiene un problema de
 * familia prefiere escoger entre tres perfiles a que le toque el que estaba
 * mirando el correo.
 *
 * Ahora `accept()` postula y `chooseLawyer()` asigna. Los datos de contacto
 * del cliente siguen saliendo una sola vez, y solo hacia el elegido: un
 * abogado postulado ve el caso, nunca a la persona.
 *
 * La condición de carrera sigue siendo real en la elección —el cliente puede
 * hacer doble clic— así que la fila del caso se bloquea con `lockForUpdate`.
 */
final class CaseAssignmentService
{
    public function __construct(private readonly MetricRecorder $metrics) {}

    /**
     * El abogado se postula.
     *
     * No asigna nada: deja constancia de que le interesa y, la primera vez,
     * le avisa al cliente que ya tiene a quién elegir.
     */
    public function accept(CaseOffer $offer, ?string $ip = null, ?string $pitch = null): AssignmentResult
    {
        $result = DB::transaction(function () use ($offer, $ip, $pitch): AssignmentResult {
            /** @var LegalCase $case */
            $case = LegalCase::query()->whereKey($offer->legal_case_id)->lockForUpdate()->firstOrFail();

            if (in_array($case->status, [CaseStatus::Cancelled, CaseStatus::Closed], true)) {
                return new AssignmentResult(AssignmentOutcome::Unavailable, $case, $offer);
            }

            if ($case->assigned_lawyer_id !== null) {
                if ($case->assigned_lawyer_id === $offer->lawyer_id) {
                    return new AssignmentResult(AssignmentOutcome::AlreadyYours, $case, $offer);
                }

                if ($offer->isPending()) {
                    $offer->update([
                        'status' => CaseOfferStatus::Superseded,
                        'responded_at' => now(),
                    ]);
                }

                return new AssignmentResult(AssignmentOutcome::TakenByOther, $case, $offer->refresh());
            }

            if ($case->hasExpired()) {
                $offer->update(['status' => CaseOfferStatus::Expired, 'responded_at' => now()]);
                $case->update(['status' => CaseStatus::Expired]);

                return new AssignmentResult(AssignmentOutcome::Expired, $case, $offer->refresh());
            }

            // Queda postulado. Los demás siguen pudiendo postularse: cuantas
            // más opciones tenga el cliente, mejor elige.
            $offer->update([
                'status' => CaseOfferStatus::Accepted,
                'responded_at' => now(),
                'response_ip' => $ip,
                'pitch' => $pitch,
            ]);

            $this->metrics->record($offer->lawyer, MetricKind::CaseApplication);

            $primero = $case->shortlisted_at === null;

            $case->update([
                'status' => CaseStatus::Shortlisted,
                'shortlisted_at' => $case->shortlisted_at ?? now(),
            ]);

            return new AssignmentResult(
                AssignmentOutcome::Shortlisted,
                $case->refresh(),
                $offer->refresh(),
                $primero ? ['notify-client'] : [],
            );
        });

        // Al cliente se le avisa una sola vez: un correo por cada abogado que
        // se postula sería ruido, y la página siempre muestra la lista al día.
        if ($result->outcome === AssignmentOutcome::Shortlisted
            && in_array('notify-client', $result->supersededOfferIds, true)) {
            Mail::to($result->case->client_email)->send(new CaseShortlistMail($result->case));
        }

        return $result;
    }

    /**
     * El cliente elige. Aquí sí se asigna y aquí sí salen sus datos.
     *
     * Se bloquea la fila porque un doble clic en el botón mandaría dos veces
     * la misma elección, y asignar dos abogados al mismo caso sería peor que
     * no asignar ninguno.
     */
    public function chooseLawyer(LegalCase $case, CaseOffer $chosen): AssignmentResult
    {
        $result = DB::transaction(function () use ($case, $chosen): AssignmentResult {
            /** @var LegalCase $locked */
            $locked = LegalCase::query()->whereKey($case->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->assigned_lawyer_id !== null) {
                return new AssignmentResult(
                    $locked->assigned_lawyer_id === $chosen->lawyer_id
                        ? AssignmentOutcome::AlreadyYours
                        : AssignmentOutcome::TakenByOther,
                    $locked,
                    $chosen,
                );
            }

            if ($chosen->status !== CaseOfferStatus::Accepted) {
                return new AssignmentResult(AssignmentOutcome::Unavailable, $locked, $chosen);
            }

            $locked->update([
                'assigned_lawyer_id' => $chosen->lawyer_id,
                'assigned_at' => now(),
                'status' => CaseStatus::Assigned,
            ]);

            $this->metrics->record($chosen->lawyer, MetricKind::CaseWon);

            $descartadas = $locked->offers()
                ->whereKeyNot($chosen->getKey())
                ->whereIn('status', [CaseOfferStatus::Pending, CaseOfferStatus::Accepted])
                ->pluck('id')
                ->all();

            if ($descartadas !== []) {
                CaseOffer::query()->whereKey($descartadas)->update([
                    'status' => CaseOfferStatus::Superseded->value,
                    'responded_at' => now(),
                ]);
            }

            $locked->assignedLawyer()->increment('cases_resolved');

            return new AssignmentResult(
                AssignmentOutcome::Won,
                $locked->refresh(),
                $chosen->refresh(),
                $descartadas,
            );
        });

        if ($result->won()) {
            $this->notifyAfterAssignment($result);
        }

        return $result;
    }

    public function decline(CaseOffer $offer, ?string $reason = null): AssignmentResult
    {
        $case = $offer->legalCase;

        if ($case->isTaken() && $case->assigned_lawyer_id !== $offer->lawyer_id) {
            return new AssignmentResult(AssignmentOutcome::TakenByOther, $case, $offer);
        }

        if ($offer->isPending()) {
            $offer->update([
                'status' => CaseOfferStatus::Declined,
                'responded_at' => now(),
                'decline_reason' => $reason,
            ]);
        } elseif ($offer->status === CaseOfferStatus::Declined && filled($reason)) {
            // El clic del correo ya marcó el rechazo; el motivo llega después,
            // desde el formulario de la página de resultado.
            $offer->update(['decline_reason' => $reason]);
        }

        return new AssignmentResult(AssignmentOutcome::Declined, $case, $offer->refresh());
    }

    /** Correos que salen una vez el caso ya tiene dueño confirmado en base. */
    private function notifyAfterAssignment(AssignmentResult $result): void
    {
        $case = $result->case->load('assignedLawyer');
        $lawyer = $case->assignedLawyer;

        if (! $lawyer) {
            return;
        }

        // 1. El cliente sabe quién lo va a atender.
        Mail::to($case->client_email)->send(new LawyerAssignedMail($case, $lawyer));

        // 2. El abogado recibe los datos de contacto del cliente.
        Mail::to($lawyer->email)->send(new CaseWonMail($case, $lawyer));

        // 3. Los demás dejan de perder tiempo en este caso.
        CaseOffer::query()
            ->whereKey($result->supersededOfferIds)
            ->with('lawyer')
            ->get()
            ->each(function (CaseOffer $superseded) use ($case): void {
                if ($superseded->lawyer?->notify_email) {
                    Mail::to($superseded->lawyer->email)->send(new CaseTakenByOtherMail($case, $superseded->lawyer));
                }
            });
    }
}
