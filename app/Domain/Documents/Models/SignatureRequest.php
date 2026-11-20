<?php

declare(strict_types=1);

namespace App\Domain\Documents\Models;

use App\Domain\Documents\Enums\SignatureStatus;
use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => SignatureStatus::class,
            'opened_at' => 'datetime',
            'signed_at' => 'datetime',
            'otp_sent_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSigned(): bool
    {
        return $this->status === SignatureStatus::Signed;
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast() && ! $this->isSigned();
    }

    public function url(): string
    {
        return route('signature.show', $this->token);
    }
}
