<?php

declare(strict_types=1);

namespace App\Domain\Payments\Models;

use App\Domain\Payments\Enums\PaymentPurpose;
use App\Domain\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'purpose' => PaymentPurpose::class,
            'payload' => 'array',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentStatus::Approved;
    }

    /** No es un cobro: son $1.500 que se anulan enseguida. */
    public function isVerification(): bool
    {
        return $this->purpose === PaymentPurpose::Verification;
    }

    /** Lo que se cobró, en pesos. */
    public function amount(): int
    {
        return (int) ($this->amount_in_cents / 100);
    }

    public function formattedAmount(): string
    {
        return '$'.number_format($this->amount(), 0, ',', '.');
    }
}
