<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Pages;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Lawyers\Services\CurrentLawyer;
use App\Domain\LegalCases\Enums\CaseOfferStatus;
use App\Domain\LegalCases\Models\CaseOffer;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Str;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Resumen';

    protected static ?string $title = 'Tu resumen';

    /**
     * El saludo dice lo que hay que hacer hoy, no solo hola.
     *
     * La mayoría de los días no hay un caso que ganar, y un panel que solo
     * felicita cuando se gana parece vacío el resto del mes. Si hay casos
     * abiertos, eso es lo que se nombra; si no, se nombra lo que sí pasó.
     */
    public function getSubheading(): ?string
    {
        $lawyer = app(CurrentLawyer::class)->get();

        if (! $lawyer) {
            return null;
        }

        $nombre = Str::before($lawyer->name, ' ');

        $abiertos = CaseOffer::query()
            ->where('lawyer_id', $lawyer->id)
            ->where('status', CaseOfferStatus::Pending)
            ->whereHas('legalCase', fn ($q) => $q
                ->whereNull('assigned_lawyer_id')
                ->where(fn ($sub) => $sub->whereNull('expires_at')->orWhere('expires_at', '>', now())))
            ->count();

        if ($abiertos > 0) {
            return $abiertos === 1
                ? "Hola, {$nombre}. Tienes un caso esperando tu postulación."
                : "Hola, {$nombre}. Tienes {$abiertos} casos esperando tu postulación.";
        }

        $vistas = app(MetricRecorder::class)->total($lawyer, MetricKind::ProfileView, now()->subDays(30));

        return $vistas > 0
            ? "Hola, {$nombre}. Este mes {$vistas} personas abrieron tu ficha."
            : "Hola, {$nombre}. Esto es lo que ha pasado con tu presencia en HazloLegal.";
    }
}
