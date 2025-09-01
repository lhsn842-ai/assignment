<?php

namespace App\Jobs;

use App\Events\ExchangeRateResultReadyEvent;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class FetchExchangeRateFromExternalProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private CONST TRIES = 3;
    private CONST CACHE_TTL = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly ExchangeRate $exchangeRate)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ExchangeRateServiceInterface $exchangeRateService, ExchangeRateRepository $repository): void
    {
        $start = microtime(true);

        try {
            $cacheKey = $exchangeRateService->getCacheKey($this->exchangeRate);

            $result = $exchangeRateService->exchangeSingleCurrency($this->exchangeRate);
            $repository->updateResult($this->exchangeRate->id, $result->getQuote() * $this->exchangeRate->amount);
            Cache::put($cacheKey, $result->getQuote(), self::CACHE_TTL);

            event(new ExchangeRateResultReadyEvent($this->exchangeRate));
        } catch (Exception $e) {
            $this->exchangeRate->increment('attempts');

            if ($this->attempts() >= self::TRIES) {
                $this->exchangeRate->update(['status' => 'failed']);
            }
            $duration = microtime(true) - $start;
            throw $e;
        }
    }

    public function test_job_puts_value_in_cache_and_dispatches_event()
    {
        Event::fake();
        Cache::flush();

        $exchangeRate = ExchangeRate::factory()->create([
            'amount' => 100,
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
        ]);

        // Run the job
        (new FetchExchangeRateFromExternalProviderJob($exchangeRate))
            ->handle(app()->make(\App\Services\ExchangeRateService\ExchangeRateServiceInterface::class),
                app()->make(\App\Repositories\ExchangeRateRepository::class));

        // Cache should now contain the result
        $cacheKey = app(\App\Services\ExchangeRateService\ExchangeRateServiceInterface::class)
            ->getCacheKey($exchangeRate);

        $this->assertTrue(Cache::has($cacheKey));

        // Event should be dispatched
        Event::assertDispatched(ExchangeRateResultReadyEvent::class);
    }

    public function test_job_handles_failures_and_marks_failed_after_retries()
    {
        Event::fake();
        Cache::flush();

        $exchangeRate = ExchangeRate::factory()->create([
            'amount' => 100,
            'status' => 'pending',
            'attempts' => 2, // already retried twice
        ]);

        // Bind a fake service that always throws
        $this->app->bind(\App\Services\ExchangeRateService\ExchangeRateServiceInterface::class, function () {
            return new class implements \App\Services\ExchangeRateService\ExchangeRateServiceInterface {
                public function exchangeSingleCurrency($exchangeRate) {
                    throw new \Exception('External service failed');
                }
                public function getCacheKey($exchangeRate): string {
                    return 'cache-key-failure';
                }
                public function getCachedValue($exchangeRate) {
                    return null;
                }
            };
        });

        $job = new FetchExchangeRateFromExternalProviderJob($exchangeRate);

        $this->expectException(\Exception::class);
        $job->handle(app(\App\Services\ExchangeRateService\ExchangeRateServiceInterface::class),
            app(\App\Repositories\ExchangeRateRepository::class));

        // The exchange rate should be marked as failed
        $exchangeRate->refresh();
        $this->assertEquals('failed', $exchangeRate->status);
    }
}
