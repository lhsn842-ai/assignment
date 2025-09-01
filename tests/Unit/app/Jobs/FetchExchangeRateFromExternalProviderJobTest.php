<?php

namespace Tests\Unit\App\Jobs;

use App\Events\ExchangeRateResultReadyEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FetchExchangeRateFromExternalProviderJobTest extends TestCase
{
    public function test_job_puts_value_in_cache_and_dispatches_event()
    {
        Event::fake();
        Cache::flush();

        $exchangeRate = ExchangeRate::factory()->create([
            'amount' => 100,
            'from_currency' => 'EUR',
            'to_currency' => 'USD',
        ]);

        (new FetchExchangeRateFromExternalProviderJob($exchangeRate))
            ->handle(
                app()->make(ExchangeRateServiceInterface::class),
                app()->make(ExchangeRateRepository::class)
            );

        $cacheKey = app(ExchangeRateServiceInterface::class)
            ->getCacheKey($exchangeRate);

        $this->assertTrue(Cache::has($cacheKey));

        Event::assertDispatched(ExchangeRateResultReadyEvent::class);
    }
}

