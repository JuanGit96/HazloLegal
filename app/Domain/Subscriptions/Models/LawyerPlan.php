<?php

declare(strict_types=1);

namespace App\Domain\Subscriptions\Models;

use App\Domain\Subscriptions\Enums\BillingPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LawyerPlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'includes_profile_page' => 'boolean',
            'includes_pdf_placement' => 'boolean',
            'includes_case_alerts' => 'boolean',
            'includes_lead_data' => 'boolean',
            'includes_featured' => 'boolean',
            'includes_blog_placement' => 'boolean',
            'is_highlighted' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(LawyerSubscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function priceFor(BillingPeriod $period): int
    {
        return $period === BillingPeriod::Annual
            ? (int) ($this->price_annual ?? $this->price_monthly * 12)
            : (int) $this->price_monthly;
    }

    /** Cuánto se ahorra al año pagando anual, para mostrarlo en la tarjeta. */
    public function annualSaving(): int
    {
        if ($this->price_annual === null || $this->price_monthly === 0) {
            return 0;
        }

        return max(0, ($this->price_monthly * 12) - $this->price_annual);
    }

    public function monthlyEquivalentOfAnnual(): ?int
    {
        return $this->price_annual === null ? null : (int) round($this->price_annual / 12);
    }
}
