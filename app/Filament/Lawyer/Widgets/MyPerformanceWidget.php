<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Widgets;

use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\LegalCases\Enums\CaseStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use App\Domain\LegalCases\Models\LegalCase;
use App\Domain\Shared\Models\LeadAccess;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Lo que el abogado necesita ver para decidir si renueva.
 *
 * Son sus números, no los del negocio: cuánta gente llegó a su perfil, cuánta
 * quiso contactarlo, cuántos casos tomó. Si estas cifras no crecen, la
 * suscripción no vale lo que cuesta, y es mejor que lo vea él antes que
 * descubrirlo cuando se va.
 */
class MyPerformanceWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $lawyer = app(CurrentLawyer::class)->get();

        if (! $lawyer) {
            return [];
        }

        $myCases = LegalCase::query()->where('assigned_lawyer_id', $lawyer->id);

        $won = (clone $myCases)->count();
        $active = (clone $myCases)->where('status', CaseStatus::Assigned)->count();

        // Cuántas invitaciones recibió y cuántas convirtió: la carrera por un
        // caso la gana quien responde primero, y eso se puede mejorar.
        $invited = CaseOffer::query()->where('lawyer_id', $lawyer->id)->count();
        $winRate = $invited > 0 ? (int) round(($won / $invited) * 100) : null;

        $contactsOpened = LeadAccess::query()
            ->where('lawyer_id', $lawyer->id)
            ->whereBetween('accessed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            Stat::make('Visitas a tu perfil', (string) ($lawyer->profile_views ?? 0))
                ->description($lawyer->is_published ? 'Perfil publicado' : 'Perfil sin publicar')
                ->descriptionIcon($lawyer->is_published ? Heroicon::OutlinedCheckBadge : Heroicon::OutlinedEyeSlash)
                ->descriptionColor($lawyer->is_published ? 'success' : 'warning')
                ->color('info'),

            Stat::make('Clics para contactarte', (string) ($lawyer->contact_clicks ?? 0))
                ->description('Gente que quiso escribirte desde el directorio')
                ->descriptionIcon(Heroicon::OutlinedCursorArrowRays)
                ->color('primary'),

            Stat::make('Casos que tomaste', (string) $won)
                ->description($winRate === null
                    ? 'Todavía sin invitaciones'
                    : "{$winRate} % de las invitaciones que recibiste")
                ->descriptionIcon(Heroicon::OutlinedBriefcase)
                ->descriptionColor($winRate !== null && $winRate >= 30 ? 'success' : 'gray')
                ->color($active > 0 ? 'success' : 'gray'),

            Stat::make('Contactos abiertos este mes', (string) $contactsOpened)
                ->description('Cada apertura queda registrada')
                ->descriptionIcon(Heroicon::OutlinedShieldCheck)
                ->color('warning'),
        ];
    }
}
