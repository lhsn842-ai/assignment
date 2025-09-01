<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class MetricsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CollectorRegistry::class, function () {
            $adapter = new Redis([
                'host' => env('REDIS_HOST', 'redis'),
                'port' => 6379,
                'password' => env('REDIS_PASSWORD', null),
                'timeout' => 0.1, // seconds
                'read_timeout' => 10, // seconds
                'persistent_connections' => false,
            ]);

            return new CollectorRegistry($adapter);
        });
    }
}