<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\AutomationPhase;
use App\Domain\Catalog\Enums\DeliverySpeed;
use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Content\Models\FaqEntry;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\Testimonial;
use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LegalDocument extends Model
{
    use HasFactory;

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
            'seo_keywords' => 'array',
            'available_express' => 'boolean',
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

    public function metaTitle(): string
    {
        return $this->seo_title ?: "{$this->name} en Colombia | Hazlo Legal";
    }

    public function metaDescription(): string
    {
        return $this->seo_description ?: Str::limit((string) $this->summary, 155);
    }
}
