<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * Dibuja la imagen que ven WhatsApp, Facebook y X al compartir un enlace.
 *
 * Se genera en vez de guardarse a mano porque es la pieza que más se olvida
 * de actualizar cuando cambia la marca, y porque el bloque del marcador
 * tiene que ajustarse al ancho real del texto: a ojo queda corto o largo.
 */
class OgImage extends Command
{
    protected $signature = 'hl:og {--salida=public/img/og-hazlolegal.png}';

    protected $description = 'Genera la imagen de previsualización social (1200x630)';

    private const ANCHO = 1200;

    private const ALTO = 630;

    private const CREMA = '#FDF8F0';

    private const TINTA = '#10151F';

    private const AZUL = '#1D4ED8';

    private const NARANJA = '#F97316';

    private const GRIS = '#475569';

    /** DejaVu es la única fuente con ñ y tildes dentro del contenedor. */
    private const FUENTE = __DIR__.'/../../../vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf';

    private const FUENTE_REGULAR = __DIR__.'/../../../vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf';

    public function handle(): int
    {
        if (! extension_loaded('imagick')) {
            $this->error('Falta la extensión imagick.');

            return self::FAILURE;
        }

        $img = new Imagick;
        $img->newImage(self::ANCHO, self::ALTO, new ImagickPixel(self::CREMA));
        $img->setImageFormat('png');

        // Logo
        $x = $this->texto($img, 'Hazlo', 72, 128, 40, self::TINTA);
        $this->marcador($img, 'Legal', $x + 10, 128, 40, self::AZUL, -2.0);

        // Titular. La última línea lleva el marcador naranja: es la promesa
        // que queremos que se lea aunque la miniatura salga pequeña.
        $this->texto($img, 'Tutelas, contratos y', 72, 278, 62, self::TINTA);
        $this->texto($img, 'derechos de petición', 72, 356, 62, self::TINTA);
        $this->marcador($img, 'listos en 24 horas', 72, 442, 62, self::NARANJA, -1.5);

        $this->texto($img, 'Formatos escritos por abogados colombianos.', 72, 520, 27, self::GRIS, self::FUENTE_REGULAR);
        $this->texto($img, 'Precio fijo publicado · Garantía de corrección por escrito', 72, 562, 27, self::GRIS, self::FUENTE_REGULAR);

        $salida = base_path((string) $this->option('salida'));
        $img->writeImage($salida);
        $img->clear();

        $this->info('Escrita '.$salida.' ('.self::ANCHO.'x'.self::ALTO.')');

        return self::SUCCESS;
    }

    /** Dibuja texto y devuelve la x donde termina. */
    private function texto(Imagick $img, string $texto, float $x, float $y, float $tam, string $color, ?string $fuente = null): float
    {
        $d = new ImagickDraw;
        $d->setFont($fuente ?? self::FUENTE);
        $d->setFontSize($tam);
        $d->setFillColor(new ImagickPixel($color));
        $img->annotateImage($d, $x, $y, 0, $texto);

        return $x + $this->ancho($img, $texto, $tam, $fuente);
    }

    /** El texto dentro de su bloque de color, inclinado como el del sitio. */
    private function marcador(Imagick $img, string $texto, float $x, float $y, float $tam, string $color, float $giro): void
    {
        $ancho = $this->ancho($img, $texto, $tam);
        $padX = $tam * 0.22;
        $arriba = $tam * 0.98;
        $abajo = $tam * 0.3;

        $cx = $x + $ancho / 2;
        $cy = $y - $arriba / 2 + $abajo / 2;

        $d = new ImagickDraw;
        $d->translate($cx, $cy);
        $d->rotate($giro);
        $d->setFillColor(new ImagickPixel($color));
        $d->roundRectangle(
            -$ancho / 2 - $padX,
            -($arriba + $abajo) / 2,
            $ancho / 2 + $padX,
            ($arriba + $abajo) / 2,
            $tam * 0.16,
            $tam * 0.16,
        );
        $d->setFont(self::FUENTE);
        $d->setFontSize($tam);
        $d->setFillColor(new ImagickPixel('#FFFFFF'));
        $d->annotation(-$ancho / 2, ($arriba + $abajo) / 2 - $abajo, $texto);
        $img->drawImage($d);
    }

    private function ancho(Imagick $img, string $texto, float $tam, ?string $fuente = null): float
    {
        $d = new ImagickDraw;
        $d->setFont($fuente ?? self::FUENTE);
        $d->setFontSize($tam);
        $m = $img->queryFontMetrics($d, $texto);

        return (float) $m['textWidth'];
    }
}
