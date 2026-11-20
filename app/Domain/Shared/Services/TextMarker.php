<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use Illuminate\Support\HtmlString;

/**
 * El marcador de la marca, aplicado a un texto que se edita desde el panel.
 *
 * El titular de la home se cambia cuando la conversión no sube, y quien lo
 * cambia no escribe HTML. Por eso el resaltado se marca con `==dos iguales==`
 * y aquí se convierte en el bloque de color, después de escapar el texto:
 * lo que entra por el panel nunca se interpreta como HTML.
 */
final class TextMarker
{
    /** Cuántas palabras resaltadas dejan de resaltar. */
    private const MAX_MARCAS = 2;

    public static function render(?string $texto, string $variante = ''): HtmlString
    {
        $clase = trim('hl '.$variante);
        $restantes = self::MAX_MARCAS;

        $html = preg_replace_callback(
            '/==(.+?)==/u',
            // El texto ya viene escapado: aquí solo se envuelve, nunca se
            // vuelve a escapar, o un «&» acabaría impreso como «&amp;».
            function (array $m) use ($clase, &$restantes): string {
                // Pasado el límite se devuelve el texto sin resaltar en vez de
                // ignorar los «==»: el titular se lee igual y nadie ve basura.
                if ($restantes-- <= 0) {
                    return $m[1];
                }

                return '<span class="'.$clase.'">'.$m[1].'</span>';
            },
            e((string) $texto),
        );

        return new HtmlString(nl2br((string) $html));
    }
}
