<?php

namespace App\Jobs;

use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchExchangeRateFromExternalProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            $result = $exchangeRateService->exchangeSingleCurrency($this->exchangeRate);
            $repository->updateResult($this->exchangeRate->id, $result->getQuote() * $this->exchangeRate->amount);

        } catch (Exception $e) {
            $this->exchangeRate->increment('attempts');

            if ($this->attempts() >= $this->tries) {
                $this->exchangeRate->update(['status' => 'failed']);
            }

            throw $e;
        } finally {
            $duration = microtime(true) - $start;
//            $metrics->observeJobExecution('CurrencyExchangeJob', $duration);
        }
    }
}
