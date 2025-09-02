<?php

namespace App\Providers;

use App\Listeners\GraphQLMetricsListener;
use App\Listeners\JobMetricsListener;
use App\Models\PersonalAccessToken;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\Services\ExchangeRateService\SwopService;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Nuwave\Lighthouse\Events\EndRequest;
use Nuwave\Lighthouse\Events\StartRequest;
use Prometheus\CollectorRegistry;

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
    public function boot(Dispatcher $events, CollectorRegistry $registry): void
    {
        Event::listen(StartRequest::class, [GraphQLMetricsListener::class, 'handleStart']);
        Event::listen(EndRequest::class, [GraphQLMetricsListener::class, 'handleEnd']);

        Event::listen(JobProcessing::class, [JobMetricsListener::class, 'handleProcessing']);
        Event::listen(JobProcessed::class, [JobMetricsListener::class, 'handleProcessed']);
        Event::listen(JobFailed::class, [JobMetricsListener::class, 'handleFailed']);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        $events->listen('*', function ($eventName, array $data) use ($registry) {
            $counter = $registry->getOrRegisterCounter(
                'app',
                'events_dispatched_total',
                'Total events dispatched',
                ['event']
            );
            $counter->inc([$eventName]);

            $histogram = $registry->getOrRegisterHistogram(
                'app',
                'event_duration_seconds',
                'Duration of event handling',
                ['event'],
                [0.001, 0.01, 0.1, 1, 3]
            );

            $start = microtime(true);
            // run the event
            $duration = microtime(true) - $start;
            $histogram->observe($duration, [$eventName]);
        });
    }
}
