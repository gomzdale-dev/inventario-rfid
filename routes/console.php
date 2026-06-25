<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Servicio comando de Isolation Forest
// Corre todos los días a las 11:00 PM automáticamente
Schedule::command('anomalias:analizar')
    ->dailyAt('23:00')
    ->name('analizar-anomalias-if')
    ->withoutOverlapping() // Evita que corra dos veces al mismo tiempo
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error(
            'Scheduler: Falló el análisis de anomalías IF'
        );
    });