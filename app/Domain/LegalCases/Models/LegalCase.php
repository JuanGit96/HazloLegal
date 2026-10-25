<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Models;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Enums\BudgetRange;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Enums\CaseUrgency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalCase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'practice_area' => PracticeArea::class,
            'status' => CaseStatus::class,
            'urgency' => CaseUrgency::class,
            'budget_range' => BudgetRange::class,
            'has_documents' => 'boolean',
            'assigned_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // -------------------------------------------------- Relaciones

    public function offers(): HasMany
    {
        return $this->hasMany(CaseOffer::class);
    }

    public function pendingOffers(): HasMany
    {
        return $this->offers()->where('status', CaseOfferStatus::Pending);
    }

    public function assignedLawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'assigned_lawyer_id');
    }

    // -------------------------------------------------- Scopes

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [CaseStatus::New->value, CaseStatus::Dispatched->value]);
    }

    public function scopeAwaitingResponse(Builder $query): Builder
    {
        return $query->where('status', CaseStatus::Dispatched);
    }

    // -------------------------------------------------- Comportamiento

    public function isTaken(): bool
    {
        return $this->assigned_lawyer_id !== null;
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast() && ! $this->isTaken();
    }

    public function responseTime(): ?string
    {
        if (! $this->assigned_at || ! $this->dispatched_at) {
            return null;
        }

        return $this->dispatched_at->diffForHumans($this->assigned_at, syntax: true, short: true);
    }
}
