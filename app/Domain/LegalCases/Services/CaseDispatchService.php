<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Services;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\Lawyers\Services\LawyerDirectory;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Mail\CaseOfferMail;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Reparte un caso a varios abogados a la vez. Gana el primero que acepta. */
final class CaseDispatchService
{
    public function __construct(private readonly LawyerDirectory $directory) {}

    public function dispatchToLawyers(LegalCase $case): int
    {
        if ($case->isTaken()) {
            return 0;
        }

        $candidates = $this->directory->candidatesFor($case->practice_area);

        if ($candidates->isEmpty()) {
            Log::warning('Caso sin abogados disponibles', ['case' => $case->reference]);

            return 0;
        }

        $sent = 0;

        foreach ($candidates as $lawyer) {
            /** @var Lawyer $lawyer */
            $offer = CaseOffer::query()->firstOrCreate(
                ['legal_case_id' => $case->id, 'lawyer_id' => $lawyer->id],
                ['status' => CaseOfferStatus::Pending],
            );

            if (! $offer->isPending()) {
                continue;
            }

            if ($lawyer->notify_email) {
                Mail::to($lawyer->email)->send(new CaseOfferMail($case, $offer, $lawyer));
            }

            $offer->update(['sent_at' => now()]);
            $sent++;
        }

        $case->update([
            'status' => CaseStatus::Dispatched,
            'dispatched_at' => now(),
            'offers_sent' => $case->offers()->count(),
        ]);

        return $sent;
    }

    /** Reenvío manual desde el panel cuando nadie respondió. */
    public function resend(LegalCase $case): int
    {
        $resent = 0;

        foreach ($case->pendingOffers()->with('lawyer')->get() as $offer) {
            if ($offer->lawyer?->notify_email) {
                Mail::to($offer->lawyer->email)->send(new CaseOfferMail($case, $offer, $offer->lawyer));
                $offer->update(['sent_at' => now()]);
                $resent++;
            }
        }

        return $resent;
    }
}
