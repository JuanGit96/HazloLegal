<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Las renovaciones se cobran una vez al día, temprano.
 *
 * A las 6 a.m. y no a medianoche porque si un cobro falla queremos que haya
 * alguien despierto para mirarlo el mismo día.
 */
Schedule::command('hl:suscripciones-cobrar')
    ->dailyAt('06:00')
    ->timezone('America/Bogota')
    ->withoutOverlapping();
