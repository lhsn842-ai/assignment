<?php

namespace App\Providers;

use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\Services\ExchangeRateService\SwopService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExchangeRateServiceInterface::class, SwopService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
