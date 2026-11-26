<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Las fechas del sitio y de los correos se leen en español.
        Carbon::setLocale(config('app.locale'));
        CarbonImmutable::setLocale(config('app.locale'));
        Date::use(CarbonImmutable::class);

        // Nada de asignación masiva accidental ni relaciones perezosas sueltas.
        Model::shouldBeStrict(! app()->isProduction());
        Model::unguard(false);

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        $this->registerAssetHelper();
        $this->registerPanelStyles();
    }

    /**
     * La hoja propia de los paneles, en los dos.
     *
     * Filament se sirve con su CSS ya compilado, que solo trae las clases
     * de sus componentes: una utilidad suelta escrita en una vista nuestra
     * no existe y el bloque sale sin formato. Compilar un tema con Vite lo
     * arreglaría, pero le añadiría un build step al despliegue.
     */
    private function registerPanelStyles(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '<link rel="stylesheet" href="'.e(self::versionedAsset('css/panel.css')).'">',
        );
    }

    /**
     * `@asset('css/site.css')`: la ruta del estático con su fecha de
     * modificación como parámetro.
     *
     * Aquí no hay build step —el sitio se despliega copiando archivos—, así
     * que sin esto un cambio de CSS no le llega al visitante hasta que haga
     * una recarga forzada, y eso no se le puede pedir a nadie.
     */
    private function registerAssetHelper(): void
    {
        Blade::directive('asset', function (string $expression) {
            return "<?php echo \App\Providers\AppServiceProvider::versionedAsset({$expression}); ?>";
        });
    }

    public static function versionedAsset(string $path): string
    {
        $file = public_path($path);
        $version = is_file($file) ? filemtime($file) : null;

        return asset($path).($version ? '?v='.$version : '');
    }
}
