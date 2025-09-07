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
            $exchangeRate = $repository->updateResult($this->exchangeRate->id, (float) $result->getQuote() * $this->exchangeRate->amount);
            Cache::put($cacheKey, $result->getQuote(), self::CACHE_TTL);

            event(new ExchangeRateResultReadyEvent(
                $exchangeRate->id,
                $exchangeRate->user_id,
                $exchangeRate->result
            ));
        } catch (Exception $e) {
            $this->exchangeRate->increment('attempts');

            if ($this->attempts() >= self::TRIES) {
                $this->exchangeRate->update(['status' => 'failed']);
            }
            $duration = microtime(true) - $start;
            throw $e;
        }
    }
}
