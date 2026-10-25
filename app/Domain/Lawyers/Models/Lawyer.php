<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Models;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Content\Models\Post;
use App\Domain\Lawyers\Enums\LawyerStatus;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lawyer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => LawyerStatus::class,
            'practice_areas' => 'array',
            'specialties' => 'array',
            'accepts_cases' => 'boolean',
            'is_author' => 'boolean',
            'notify_email' => 'boolean',
            'notify_whatsapp' => 'boolean',
            'rating' => 'decimal:1',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $lawyer): void {
            if (blank($lawyer->slug)) {
                $lawyer->slug = Str::slug($lawyer->name);
            }
        });
    }

    // -------------------------------------------------- Relaciones

    public function caseOffers(): HasMany
    {
        return $this->hasMany(CaseOffer::class);
    }

    public function assignedCases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'assigned_lawyer_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_lawyer_id');
    }

    // -------------------------------------------------- Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LawyerStatus::Active);
    }

    /** Abogados que pueden recibir el correo de un caso nuevo. */
    public function scopeAvailableForCases(Builder $query): Builder
    {
        return $query->active()->where('accepts_cases', true);
    }

    public function scopeInArea(Builder $query, PracticeArea $area): Builder
    {
        return $query->whereJsonContains('practice_areas', $area->value);
    }

    public function scopeAuthors(Builder $query): Builder
    {
        return $query->where('is_author', true);
    }

    // -------------------------------------------------- Atributos derivados

    public function activeCasesCount(): int
    {
        return $this->assignedCases()
            ->whereIn('status', [CaseStatus::Assigned->value])
            ->count();
    }

    public function hasCapacity(): bool
    {
        return $this->activeCasesCount() < $this->max_active_cases;
    }

    public function practiceAreaEnums(): array
    {
        return collect($this->practice_areas ?? [])
            ->map(fn (string $value) => PracticeArea::tryFrom($value))
            ->filter()
            ->values()
            ->all();
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    /** Credencial completa para pie de artículos y correos. */
    public function credentialLine(): string
    {
        $parts = array_filter([
            $this->headline,
            $this->professional_card ? "T.P. {$this->professional_card} del C.S. de la J." : null,
        ]);

        return implode(' · ', $parts);
    }
}
