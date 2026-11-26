<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Que el panel se vea.
 *
 * Filament se sirve con su CSS ya compilado, que solo trae las clases de sus
 * propios componentes. Una utilidad suelta de Tailwind escrita en una vista
 * nuestra no existe en ninguna hoja, así que el bloque sale como texto plano
 * y nadie se entera hasta que alguien abre esa pantalla. Estas dos pruebas
 * son el aviso.
 */
class PanelStylesTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_paneles_enlazan_su_propia_hoja_de_estilos(): void
    {
        foreach (['/portal/login', '/admin/login'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('css/panel.css', escape: false);
        }
    }

    public function test_las_vistas_propias_no_usan_utilidades_que_nadie_compila(): void
    {
        $permitidas = ['is-on', 'is-off'];
        $sueltas = [];

        foreach ($this->vistasDelPanel() as $ruta => $contenido) {
            foreach ($this->clasesDe($contenido) as $clase) {
                if (str_starts_with($clase, 'hl-') || str_starts_with($clase, 'fi-')) {
                    continue;
                }

                if (in_array($clase, $permitidas, true)) {
                    continue;
                }

                $sueltas[] = basename($ruta).': '.$clase;
            }
        }

        $this->assertSame([], $sueltas, implode("\n", [
            'Estas clases no las compila nadie y no van a pintar nada.',
            'Añade lo que necesites a public/css/panel.css con prefijo hl-.',
            ...$sueltas,
        ]));
    }

    /** @return array<string, string> */
    private function vistasDelPanel(): array
    {
        $vistas = [];

        $directorio = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views/filament')),
        );

        foreach ($directorio as $archivo) {
            if ($archivo->isFile() && str_ends_with($archivo->getFilename(), '.blade.php')) {
                $vistas[$archivo->getPathname()] = (string) file_get_contents($archivo->getPathname());
            }
        }

        return $vistas;
    }

    /** @return list<string> */
    private function clasesDe(string $contenido): array
    {
        $clases = [];

        // Los `class="…"` literales. Los que llevan Blade dentro se saltan:
        // ahí la clase la decide PHP y no se puede leer desde aquí.
        preg_match_all('/class="([^"{}@]*)"/', $contenido, $literales);

        foreach ($literales[1] as $lista) {
            $clases = [...$clases, ...preg_split('/\s+/', trim($lista), -1, PREG_SPLIT_NO_EMPTY)];
        }

        // Y las claves de los `@class([...])`, que son las condicionales.
        preg_match_all('/@class\\(\\[(.*?)\\]\\)/s', $contenido, $condicionales);

        foreach ($condicionales[1] as $bloque) {
            // Fuera los índices de arreglo del condicional (`$x['y']`): son
            // del PHP de la derecha, no clases de la izquierda.
            $bloque = (string) preg_replace("/\\[\\s*'[^']*'\\s*\\]/", '', $bloque);

            preg_match_all("/'([^']+)'/", $bloque, $entradas);

            foreach ($entradas[1] as $entrada) {
                $clases = [...$clases, ...preg_split('/\\s+/', trim($entrada), -1, PREG_SPLIT_NO_EMPTY)];
            }
        }

        return $clases;
    }
}
