<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Models;

use App\Domain\Analytics\Enums\MetricKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** El agregado de un día para un sujeto. Nunca una visita suelta. */
class DailyMetric extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'metric' => MetricKind::class,
            'count' => 'integer',
        ];
    }

    public function scopeFor(Builder $query, ?Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject?->getMorphClass() ?? 'site')
            ->where('subject_id', $subject?->getKey() ?? 0);
    }

    public function scopeOfKind(Builder $query, MetricKind|array $kinds): Builder
    {
        $valores = collect(is_array($kinds) ? $kinds : [$kinds])
            ->map(fn (MetricKind $kind) => $kind->value)
            ->all();

        return $query->whereIn('metric', $valores);
    }
}
