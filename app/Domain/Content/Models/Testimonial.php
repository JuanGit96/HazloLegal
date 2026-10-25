<?php

declare(strict_types=1);

namespace App\Domain\Content\Models;

use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function scopeOnHome(Builder $query): Builder
    {
        return $query->published()->where('show_on_home', true);
    }

    public function initials(): string
    {
        return Str::of($this->author_name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $p) => Str::upper(Str::substr($p, 0, 1)))
            ->implode('');
    }
}
