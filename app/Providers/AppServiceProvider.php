<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
    }
}
