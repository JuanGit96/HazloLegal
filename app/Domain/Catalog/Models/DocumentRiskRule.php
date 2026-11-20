<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\RiskLevel;
use App\Domain\Catalog\Enums\RiskRuleKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRiskRule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => RiskRuleKind::class,
            'level' => RiskLevel::class,
            'escalates_with_others' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeOfKind(Builder $query, RiskRuleKind $kind): Builder
    {
        return $query->where('kind', $kind);
    }
}
