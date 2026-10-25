<?php

declare(strict_types=1);

namespace App\Domain\Shared\Models;

use App\Domain\Orders\Models\Order;
use App\Domain\Shared\Enums\ClaimReason;
use App\Domain\Shared\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuaranteeClaim extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'reason' => ClaimReason::class,
            'status' => ClaimStatus::class,
            'refund_issued' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [ClaimStatus::Open->value, ClaimStatus::InReview->value]);
    }
}
