<?php

namespace Tests\Unit\App\Jobs;

use App\Events\ExchangeRateResultReadyEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\ValueObjects\SwopSingleCurrencyExchangeRateVO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class FetchExchangeRateFromExternalProviderJobTest extends TestCase
{
    public function test_job_puts_value_in_cache_and_dispatches_event()
    {
        Event::fake();
        Cache::flush();

        /** @var ExchangeRate $exchangeRate */
        $exchangeRate = ExchangeRate::factory()->create([
            'amount' => 100,
            'from_currency' => 'EUR',
            'to_currency' => 'USD',
        ]);
        $cacheKey = app(ExchangeRateServiceInterface::class)
            ->getCacheKey($exchangeRate);
        $mockedExchangeRateService = Mockery::mock(ExchangeRateServiceInterface::class);
        $mockedExchangeRateService->shouldReceive('exchangeSingleCurrency')
            ->andReturn(new SwopSingleCurrencyExchangeRateVO(1.1, Carbon::now()->toDateString()));
        $mockedExchangeRateService->shouldReceive('getCacheKey')->andReturn($cacheKey);
        (new FetchExchangeRateFromExternalProviderJob($exchangeRate))
            ->handle(
                $mockedExchangeRateService,
                app()->make(ExchangeRateRepository::class)
            );

        Event::assertDispatched(ExchangeRateResultReadyEvent::class);
    }
}

