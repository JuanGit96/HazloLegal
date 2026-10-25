<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Genera referencias legibles y consecutivas por año: HL-2026-00042.
 * El cliente las dicta por teléfono, así que evitamos caracteres ambiguos.
 */
final class ReferenceGenerator
{
    public function for(string $modelClass, string $prefix, string $column = 'reference'): string
    {
        $year = now()->format('Y');

        /** @var Model $model */
        $model = new $modelClass;

        $count = DB::table($model->getTable())
            ->where($column, 'like', "{$prefix}-{$year}-%")
            ->count();

        do {
            $count++;
            $candidate = sprintf('%s-%s-%05d', $prefix, $year, $count);
        } while (DB::table($model->getTable())->where($column, $candidate)->exists());

        return $candidate;
    }
}
