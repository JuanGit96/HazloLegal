<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Services;

use App\Domain\LegalCases\Data\CaseIntakeData;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Mail\CaseReceivedMail;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\Shared\Services\ReferenceGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class CaseIntakeService
{
    public function __construct(
        private readonly ReferenceGenerator $references,
        private readonly CaseDispatchService $dispatcher,
    ) {}

    /**
     * Registra el caso, acusa recibo al cliente y lo reparte a los abogados.
     * El cliente nunca espera: el acuse sale en el mismo request.
     */
    public function register(CaseIntakeData $data): LegalCase
    {
        $case = DB::transaction(fn () => LegalCase::query()->create([
            'reference' => $this->references->for(LegalCase::class, 'CC'),
            'client_name' => $data->clientName,
            'client_email' => $data->clientEmail,
            'client_phone' => $data->clientPhone,
            'client_city' => $data->clientCity,
            'practice_area' => $data->practiceArea,
            'title' => $data->title,
            'description' => $data->description,
            'urgency' => $data->urgency,
            'budget_range' => $data->budgetRange,
            'has_documents' => $data->hasDocuments,
            'deadline_note' => $data->deadlineNote,
            'status' => CaseStatus::New,
            'expires_at' => now()->addHours((int) config('hazlolegal.cases.offer_ttl_hours', 48)),
            ...$data->utm,
        ]));

        Mail::to($case->client_email)->send(new CaseReceivedMail($case));

        $this->dispatcher->dispatchToLawyers($case);

        return $case->refresh();
    }
}
