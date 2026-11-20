<?php

declare(strict_types=1);

namespace App\Domain\Shared\Models;

use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Content\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'is_subscribed' => 'boolean',
            'consent_marketing' => 'boolean',
            'consent_share_with_lawyers' => 'boolean',
            'consent_revoked_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'practice_area' => \App\Domain\Catalog\Enums\PracticeArea::class,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Orders\Models\Order::class);
    }

    public function accesses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadAccess::class);
    }

    /**
     * Leads que un abogado puede ver.
     *
     * Solo los que autorizaron expresamente compartir su caso y no han
     * revocado. Sin esto el directorio sería una venta de datos.
     */
    public function scopeShareable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('consent_share_with_lawyers', true)
            ->whereNull('consent_revoked_at');
    }

    public function isShareable(): bool
    {
        return $this->consent_share_with_lawyers && $this->consent_revoked_at === null;
    }
}
