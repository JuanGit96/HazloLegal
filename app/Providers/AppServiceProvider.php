<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Blade;
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
