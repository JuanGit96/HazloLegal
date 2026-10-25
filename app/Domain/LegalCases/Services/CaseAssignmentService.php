<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Services;

use App\Domain\LegalCases\Data\AssignmentResult;
use App\Domain\LegalCases\Enums\AssignmentOutcome;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Mail\CaseTakenByOtherMail;
use App\Domain\LegalCases\Mail\CaseWonMail;
use App\Domain\LegalCases\Mail\LawyerAssignedMail;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Resuelve la carrera por un caso.
 *
 * La condición de carrera es real: varios abogados pueden hacer clic en el
 * mismo segundo. Se bloquea la fila del caso con `lockForUpdate` dentro de una
 * transacción, así sólo un request puede asignarlo; los demás leen que ya
 * tiene dueño y reciben "lo tomó otro".
 */
final class CaseAssignmentService
{
    public function accept(CaseOffer $offer, ?string $ip = null): AssignmentResult
    {
        $result = DB::transaction(function () use ($offer, $ip): AssignmentResult {
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

            // Gana la carrera.
            $case->update([
                'assigned_lawyer_id' => $offer->lawyer_id,
                'assigned_at' => now(),
                'status' => CaseStatus::Assigned,
            ]);

            $offer->update([
                'status' => CaseOfferStatus::Accepted,
                'responded_at' => now(),
                'response_ip' => $ip,
            ]);

            $supersededIds = $case->offers()
                ->whereKeyNot($offer->getKey())
                ->where('status', CaseOfferStatus::Pending)
                ->pluck('id')
                ->all();

            if ($supersededIds !== []) {
                CaseOffer::query()->whereKey($supersededIds)->update([
                    'status' => CaseOfferStatus::Superseded->value,
                    'responded_at' => now(),
                ]);
            }

            $case->assignedLawyer()->increment('cases_resolved');

            return new AssignmentResult(
                AssignmentOutcome::Won,
                $case->refresh(),
                $offer->refresh(),
                $supersededIds,
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
