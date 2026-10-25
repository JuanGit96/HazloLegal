<?php

declare(strict_types=1);

namespace App\Domain\LegalCases\Models;

use App\Domain\Lawyers\Models\Lawyer;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CaseOffer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => CaseOfferStatus::class,
            'sent_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $offer): void {
            $offer->token ??= Str::random(48);
        });
    }

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function isPending(): bool
    {
        return $this->status === CaseOfferStatus::Pending;
    }

    public function acceptUrl(): string
    {
        return route('cases.offer.respond', ['token' => $this->token, 'decision' => 'accept']);
    }

    public function declineUrl(): string
    {
        return route('cases.offer.respond', ['token' => $this->token, 'decision' => 'decline']);
    }
}
