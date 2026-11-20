<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Models;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Content\Models\Post;
use App\Domain\Lawyers\Enums\LawyerKind;
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
            'kind' => LawyerKind::class,
            'practice_areas' => 'array',
            'specialties' => 'array',
            'accepts_cases' => 'boolean',
            'is_author' => 'boolean',
            'notify_email' => 'boolean',
            'notify_whatsapp' => 'boolean',
            'services' => 'array',
            'languages' => 'array',
            'offers_virtual' => 'boolean',
            'offers_free_first_call' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'public_rating' => 'decimal:1',
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

    public function isFirm(): bool
    {
        return $this->kind === LawyerKind::Firm;
    }

    /**
     * Cómo se le llama en el sitio.
     *
     * Decirle «abogado» a una firma de veinte personas suena a error, y
     * llamarle «firma» a alguien que ejerce solo, a inflado.
     */
    public function kindLabel(): string
    {
        return ($this->kind ?? LawyerKind::Individual)->publicLabel();
    }

    /** El usuario del panel con el que este abogado entra a ver lo suyo. */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Domain\Subscriptions\Models\LawyerSubscription::class);
    }

    public function subscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domain\Subscriptions\Models\LawyerSubscription::class)
            ->active()
            ->latestOfMany();
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

    /** Perfiles que se muestran en el directorio público. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->active()->where('is_published', true);
    }

    /** Orden del directorio: destacados primero, luego por prioridad de plan. */
    public function scopeDirectoryOrder(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderByDesc('public_rating')
            ->orderByDesc('cases_resolved');
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

    public function profileUrl(): string
    {
        return route('lawyers.show', $this->slug);
    }

    public function displayName(): string
    {
        return $this->firm_name ?: $this->name;
    }

    /** ¿El plan vigente le concede este beneficio? */
    public function can(string $feature): bool
    {
        return (bool) $this->subscription?->allows($feature);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    /**
     * Línea de presentación para artículos y correos.
     *
     * Deliberadamente NO incluye la tarjeta profesional: el número se guarda
     * para control interno del equipo, pero no se publica ni se usa como
     * argumento comercial.
     */
    public function credentialLine(): string
    {
        return collect([$this->headline, $this->city])->filter()->implode(' · ');
    }
}
