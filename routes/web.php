<?php

use Illuminate\Support\Facades\Route;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/metrics', function (\Prometheus\CollectorRegistry $registry) {
    $counter = $registry->getOrRegisterCounter(
        'app',
        'requests_total',
        'Total number of requests',
        ['route']
    );

    $counter->inc([request()->path()]);

    $gauge = $registry->getOrRegisterGauge(
        'app',
        'random_gauge',
        'A random gauge for testing',
        []
    );
    $gauge->set(rand(0, 100));

    $renderer = new RenderTextFormat();
    $metrics = $registry->getMetricFamilySamples();

    return response($renderer->render($metrics))
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});