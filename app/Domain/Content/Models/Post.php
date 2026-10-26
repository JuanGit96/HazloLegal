<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Enums\PostStatus;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'seo_keywords' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            $post->reading_minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->body)) / 200));
        });
    }

    // -------------------------------------------------- Relaciones

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'author_lawyer_id');
    }

    public function relatedDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'related_document_id');
    }

    // -------------------------------------------------- Scopes

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    // -------------------------------------------------- Comportamiento

    public function url(): string
    {
        return route('blog.show', $this->slug);
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: "{$this->title} | HazloLegal";
    }

    public function metaDescription(): string
    {
        return $this->seo_description ?: Str::limit(strip_tags((string) $this->excerpt), 155);
    }

    public function hasLeadMagnet(): bool
    {
        return filled($this->lead_magnet_title);
    }
}
