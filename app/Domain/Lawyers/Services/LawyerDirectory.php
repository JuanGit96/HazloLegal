<?php

declare(strict_types=1);

namespace App\Domain\Lawyers\Services;

use App\Domain\Catalog\Enums\PracticeArea;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Support\Collection;

final class LawyerDirectory
{
    /**
     * Candidatos para un caso: primero los especialistas del área con cupo
     * libre; si no alcanzan, se completa con el resto del equipo activo.
     */
    public function candidatesFor(PracticeArea $area, ?int $limit = null): Collection
    {
        $limit ??= (int) config('hazlolegal.cases.max_lawyers_notified', 8);

        $specialists = Lawyer::query()
            ->availableForCases()
            ->inArea($area)
            ->orderByDesc('rating')
            ->orderByDesc('cases_resolved')
            ->get()
            ->filter(fn (Lawyer $lawyer) => $lawyer->hasCapacity());

        if ($specialists->count() >= $limit || ! config('hazlolegal.cases.notify_fallback_all_areas')) {
            return $specialists->take($limit)->values();
        }

        $fallback = Lawyer::query()
            ->availableForCases()
            ->whereNotIn('id', $specialists->pluck('id'))
            ->orderByDesc('rating')
            ->get()
            ->filter(fn (Lawyer $lawyer) => $lawyer->hasCapacity());

        return $specialists->concat($fallback)->take($limit)->values();
    }

    public function publicTeam(int $limit = 6): Collection
    {
        return Lawyer::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('years_experience')
            ->limit($limit)
            ->get();
    }

    public function authors(): Collection
    {
        return Lawyer::query()->active()->authors()->orderBy('name')->get();
    }
}
