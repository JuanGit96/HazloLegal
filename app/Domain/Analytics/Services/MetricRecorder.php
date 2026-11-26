<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Services;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Models\DailyMetric;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Cuenta sin guardar a nadie.
 *
 * Suma al agregado del día y no escribe una fila por visita: ni IP, ni
 * identificador, ni forma de reconstruir quién vio qué. Eso no es un recorte
 * de alcance, es la decisión: contar sin identificar no es tratamiento de
 * datos personales, así que estas cifras incluyen también a quien rechazó
 * las cookies —que es casi todo el mundo— y por eso se le pueden enseñar al
 * abogado sin matizarlas.
 */
final class MetricRecorder
{
    /**
     * Suma uno (o lo que se diga) al día de hoy.
     *
     * En dos pasos y no en un `upsert` con expresión: la expresión
     * «count = count + n» se escribe distinto en MariaDB y en SQLite, y los
     * tests corren sobre SQLite. Primero se asegura que la fila exista
     * —`insertOrIgnore` respeta el índice único, así que dos visitas
     * simultáneas no crean dos filas— y después se sube el contador con un
     * UPDATE, que sí es atómico en cualquier motor.
     */
    public function record(?Model $subject, MetricKind $kind, int $amount = 1, ?CarbonInterface $on = null): void
    {
        $this->bump([$this->key($subject)], $kind, $amount, $on);
    }

    /**
     * Lo mismo para varios sujetos de golpe.
     *
     * Un listado del directorio muestra doce abogados: doce idas y vueltas
     * por visita de página convertirían una métrica en un problema de
     * rendimiento, y entonces habría que quitarla. Así son dos consultas,
     * vengan uno o doce.
     *
     * @param  iterable<Model>  $subjects
     */
    public function recordMany(iterable $subjects, MetricKind $kind, int $amount = 1): void
    {
        $claves = collect($subjects)
            ->filter(fn ($subject) => $subject instanceof Model)
            ->map(fn (Model $subject) => $this->key($subject))
            ->unique(fn (array $clave) => $clave['subject_type'].':'.$clave['subject_id'])
            ->values()
            ->all();

        $this->bump($claves, $kind, $amount);
    }

    /**
     * @param  list<array{subject_type: string, subject_id: int}>  $claves
     */
    private function bump(array $claves, MetricKind $kind, int $amount, ?CarbonInterface $on = null): void
    {
        if ($claves === [] || $amount === 0) {
            return;
        }

        $fecha = ($on ?? now())->toDateString();

        DailyMetric::query()->insertOrIgnore(array_map(fn (array $clave) => [
            ...$clave,
            'date' => $fecha,
            'metric' => $kind->value,
            'count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $claves));

        // Agrupado por tipo: una llamada puede traer abogados y artículos
        // mezclados, y un `whereIn` sobre ids de tipos distintos sumaría a
        // quien no tocaba.
        foreach (collect($claves)->groupBy('subject_type') as $tipo => $delTipo) {
            DailyMetric::query()
                ->where('date', $fecha)
                ->where('metric', $kind->value)
                ->where('subject_type', $tipo)
                ->whereIn('subject_id', $delTipo->pluck('subject_id')->all())
                ->increment('count', $amount, ['updated_at' => now()]);
        }
    }

    /** @return array{subject_type: string, subject_id: int} */
    private function key(?Model $subject): array
    {
        return [
            'subject_type' => $subject?->getMorphClass() ?? 'site',
            'subject_id' => (int) ($subject?->getKey() ?? 0),
        ];
    }

    /** El total de una métrica, opcionalmente desde una fecha. */
    public function total(?Model $subject, MetricKind|array $kinds, ?CarbonInterface $since = null): int
    {
        return (int) DailyMetric::query()
            ->for($subject)
            ->ofKind($kinds)
            ->when($since, fn ($q) => $q->where('date', '>=', $since->toDateString()))
            ->sum('count');
    }

    /**
     * La serie diaria, con los días vacíos rellenos.
     *
     * Sin rellenar, una gráfica de barras se come los días sin visitas y
     * dibuja una línea que sube cuando en realidad estuvo plana.
     *
     * @return array<string, int> fecha (Y-m-d) => cantidad
     */
    public function series(?Model $subject, MetricKind|array $kinds, int $days = 30): array
    {
        $desde = now()->startOfDay()->subDays($days - 1);

        $registradas = DailyMetric::query()
            ->for($subject)
            ->ofKind($kinds)
            ->where('date', '>=', $desde->toDateString())
            ->groupBy('date')
            ->selectRaw('date, SUM(count) as total')
            ->pluck('total', 'date')
            ->mapWithKeys(fn ($total, $fecha) => [Carbon::parse($fecha)->toDateString() => (int) $total]);

        $serie = [];

        for ($dia = 0; $dia < $days; $dia++) {
            $fecha = $desde->copy()->addDays($dia)->toDateString();
            $serie[$fecha] = (int) ($registradas[$fecha] ?? 0);
        }

        return $serie;
    }

    /**
     * Varias métricas de un sujeto, cada una con su total.
     *
     * @return Collection<string, int>
     */
    public function totals(?Model $subject, array $kinds, ?CarbonInterface $since = null): Collection
    {
        $sumas = DailyMetric::query()
            ->for($subject)
            ->ofKind($kinds)
            ->when($since, fn ($q) => $q->where('date', '>=', $since->toDateString()))
            ->groupBy('metric')
            ->selectRaw('metric, SUM(count) as total')
            ->pluck('total', 'metric');

        return collect($kinds)->mapWithKeys(fn (MetricKind $kind) => [
            $kind->value => (int) ($sumas[$kind->value] ?? 0),
        ]);
    }

    /**
     * Cuánto cambió respecto del periodo anterior, en porcentaje.
     *
     * Null cuando antes no había nada: «subió un infinito por ciento» no es
     * un dato, es un adorno.
     */
    public function trend(?Model $subject, MetricKind|array $kinds, int $days = 30): ?int
    {
        $ahora = $this->total($subject, $kinds, now()->subDays($days));
        $antes = $this->total($subject, $kinds, now()->subDays($days * 2))
            - $ahora;

        if ($antes <= 0) {
            return null;
        }

        return (int) round((($ahora - $antes) / $antes) * 100);
    }
}
