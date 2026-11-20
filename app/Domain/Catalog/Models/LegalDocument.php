<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\AutomationPhase;
use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Shared\Concerns\RoundsPrices;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\Testimonial;
use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LegalDocument extends Model
{
    use HasFactory;
    use RoundsPrices;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'practice_area' => PracticeArea::class,
            'automation_phase' => AutomationPhase::class,
            'what_you_get' => 'array',
            'required_info' => 'array',
            'use_cases' => 'array',
            'not_included' => 'array',
            'faqs' => 'array',
            'filing_steps' => 'array',
            'filing_channels' => 'array',
            'instructions_warnings' => 'array',
            'instructions_attachments' => 'array',
            'glossary' => 'array',
            'template_fields' => 'array',
            'seo_keywords' => 'array',
            'available_express' => 'boolean',
            'self_service_enabled' => 'boolean',
            'is_free' => 'boolean',
            'supports_signature' => 'boolean',
            'requires_lawyer_review' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $document): void {
            if (blank($document->slug)) {
                $document->slug = Str::slug($document->name);
            }
        });
    }

    // -------------------------------------------------- Relaciones

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Los adicionales ofrecidos para este trámite, en orden de venta. */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(ServiceAddon::class)
            ->withPivot(['price', 'is_available', 'is_recommended', 'is_preselected', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function availableAddons(): BelongsToMany
    {
        return $this->addons()
            ->wherePivot('is_available', true)
            ->where('service_addons.is_active', true);
    }

    /** Las casillas de petición de este trámite. */
    public function clauses(): HasMany
    {
        return $this->hasMany(DocumentClause::class)->orderBy('sort_order');
    }

    /** Las reglas del semáforo. */
    public function riskRules(): HasMany
    {
        return $this->hasMany(DocumentRiskRule::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->where('is_published', true);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'related_document_id');
    }

    public function faqEntries(): HasMany
    {
        return $this->hasMany(FaqEntry::class)->where('is_published', true)->orderBy('sort_order');
    }

    // -------------------------------------------------- Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInArea(Builder $query, PracticeArea $area): Builder
    {
        return $query->where('practice_area', $area);
    }

    // -------------------------------------------------- Comportamiento

    public function url(): string
    {
        return route('documents.show', $this->slug);
    }

    public function emojiOrArea(): string
    {
        return $this->emoji ?: $this->practice_area->emoji();
    }

    public function isAutomated(): bool
    {
        return $this->automation_phase === AutomationPhase::Automated;
    }

    public function allowsSpeed(DeliverySpeed $speed): bool
    {
        return $speed === DeliverySpeed::Standard || $this->available_express;
    }

    /** Las FAQ del documento viven en JSON (editables) o en la tabla relacionada. */
    public function resolvedFaqs(): array
    {
        $fromJson = collect($this->faqs ?? [])
            ->map(fn (array $faq) => ['question' => $faq['question'] ?? '', 'answer' => $faq['answer'] ?? ''])
            ->filter(fn (array $faq) => filled($faq['question']));

        $fromTable = $this->faqEntries->map(fn (FaqEntry $faq) => [
            'question' => $faq->question,
            'answer' => $faq->answer,
        ]);

        return $fromJson->concat($fromTable)->values()->all();
    }

    /** ¿Se puede pedir solo, sin abogado? La tutela, por ejemplo, no. */
    public function allowsSelfService(): bool
    {
        return $this->self_service_enabled;
    }

    public function isFreeTier(): bool
    {
        return $this->self_service_enabled && (int) $this->base_price === 0;
    }

    /** Precio tachado: el configurado a mano o el que resulta del descuento. */
    public function compareAtPrice(): ?int
    {
        if ($this->compare_at_price) {
            return (int) $this->compare_at_price;
        }

        if ($this->discount_percentage > 0 && $this->base_price > 0) {
            return $this->roundToPricePoint($this->base_price / (1 - $this->discount_percentage / 100));
        }

        return null;
    }

    /** El precio más bajo con el que se puede anunciar el trámite. */
    public function entryPrice(): int
    {
        return $this->self_service_enabled
            ? (int) $this->base_price
            : (int) ($this->loadMissing('availableAddons')->availableAddons
                ->min(fn ($a) => $a->priceFor($this)) ?? 0);
    }

    /**
     * Lo que se pinta en una tarjeta de catálogo.
     *
     * Un trámite sin autoservicio no tiene precio propio: lo que vale es el
     * acompañamiento, así que se anuncia «Desde» con el adicional más barato.
     */
    public function priceLabel(): string
    {
        $price = $this->entryPrice();

        if (! $this->self_service_enabled) {
            return $price > 0 ? 'Desde $'.number_format($price, 0, ',', '.') : 'Cotizar';
        }

        return $price === 0 ? 'Gratis' : '$'.number_format($price, 0, ',', '.');
    }

    public function deliveryLabel(): string
    {
        return match (true) {
            $this->delivery_hours <= 1 => 'Inmediato',
            $this->delivery_hours <= 24 => '24 horas hábiles',
            default => "{$this->delivery_hours} horas",
        };
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: "{$this->name} en Colombia | HazloLegal";
    }

    public function metaDescription(): string
    {
        return $this->seo_description ?: Str::limit((string) $this->summary, 155);
    }
}
