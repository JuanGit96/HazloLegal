<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Analytics\Enums\MetricKind;
use App\Domain\Analytics\Models\DailyMetric;
use App\Domain\Analytics\Services\MetricRecorder;
use App\Domain\Content\Models\Post;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Seeder;

/**
 * Cifras de demostración para el portal del abogado.
 *
 * **Son inventadas.** Existen para poder mirar el panel y ver que las
 * gráficas y los comparativos funcionan antes de que el sitio tenga tráfico
 * real; en producción esta clase no se ejecuta y las cifras salen de las
 * visitas de verdad.
 *
 * Se reparten con una curva creciente y con ruido, no planas: una serie
 * perfecta delata el relleno y, peor, haría pasar por buena una gráfica que
 * en realidad no sabe dibujar variaciones.
 */
class DemoMetricsSeeder extends Seeder
{
    public function run(): void
    {
        // Borra lo que haya: en producción eso serían las cifras reales del
        // portal de cada abogado, así que ahí no se ejecuta pase lo que pase.
        if (app()->isProduction()) {
            $this->command?->warn('DemoMetricsSeeder no se ejecuta en producción: borraría las métricas reales.');

            return;
        }

        $metrics = app(MetricRecorder::class);

        // Se limpia primero para poder volver a sembrar sin acumular.
        DailyMetric::query()->delete();

        $abogados = Lawyer::query()->published()->get();
        $posts = Post::query()->published()->with('author')->get();

        foreach (range(89, 0) as $atras) {
            $dia = now()->subDays($atras);
            // La red crece: los días recientes pesan más que los viejos.
            $crecimiento = 0.5 + ((90 - $atras) / 90);

            foreach ($abogados as $indice => $abogado) {
                // Los primeros de la lista están destacados y reciben más.
                $peso = max(0.4, 1.4 - ($indice * 0.18));

                $impresiones = (int) round(random_int(6, 22) * $crecimiento * $peso);
                $vistas = (int) round($impresiones * (random_int(14, 28) / 100));
                $clics = (int) round($vistas * (random_int(8, 22) / 100));

                $metrics->record($abogado, MetricKind::DirectoryImpression, $impresiones, $dia);
                $metrics->record($abogado, MetricKind::ProfileView, max(0, $vistas), $dia);
                $metrics->record($abogado, MetricKind::ContactClick, max(0, $clics), $dia);

                if (random_int(1, 10) > 7) {
                    $metrics->record($abogado, MetricKind::PdfPlacement, random_int(1, 4), $dia);
                }

                if (random_int(1, 30) === 1) {
                    $metrics->record($abogado, MetricKind::CaseInvitation, 1, $dia);
                }
            }

            foreach ($posts as $post) {
                $lecturas = (int) round(random_int(2, 14) * $crecimiento);

                $metrics->record($post, MetricKind::ArticleView, $lecturas, $dia);

                if ($post->author_lawyer_id !== null) {
                    $metrics->record($post->author, MetricKind::ArticleView, $lecturas, $dia);
                }
            }
        }

        $this->command?->info('Métricas de demostración sembradas para 90 días. No son reales.');
    }
}
