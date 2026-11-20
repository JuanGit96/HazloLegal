<?php

declare(strict_types=1);

namespace App\Domain\Shared\Models;

use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Enums\ContactStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kind' => ContactKind::class,
            'status' => ContactStatus::class,
            'confirms_ownership' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn(
            'status',
            collect(ContactStatus::openStates())->map(fn (ContactStatus $s) => $s->value)
        );
    }

    public function scopeOfKind(Builder $query, ContactKind $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    /** La fecha en que se vence el plazo de respuesta. */
    public function dueAt(): ?\Carbon\CarbonInterface
    {
        return $this->created_at?->addWeekdays($this->kind->responseDays());
    }

    public function isOverdue(): bool
    {
        return $this->dueAt()?->isPast() === true
            && in_array($this->status, ContactStatus::openStates(), true);
    }
}
