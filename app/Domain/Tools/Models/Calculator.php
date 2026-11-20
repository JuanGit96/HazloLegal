<?php

declare(strict_types=1);

namespace App\Domain\Tools\Models;

use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Calculator extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'faqs' => 'array',
            'captures_lead' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $calculator): void {
            if (blank($calculator->slug)) {
                $calculator->slug = Str::slug($calculator->name);
            }
        });
    }

    public function relatedDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'related_document_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function url(): string
    {
        return route('tools.calculator', $this->slug);
    }

    /** Parámetro del año (SMLMV, auxilio de transporte…) con respaldo. */
    public function param(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: "{$this->name} en Colombia | Calculadora gratis";
    }
}
