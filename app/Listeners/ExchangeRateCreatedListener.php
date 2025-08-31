<?php

namespace App\Listeners;

use App\Events\ExchangeRateCreatedEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ExchangeRateCreatedListener
{
    /**
     * Handle the event.
     */
    public function handle(ExchangeRateCreatedEvent $event): void
    {
        FetchExchangeRateFromExternalProviderJob::dispatch($event->exchangeRate);
    }
}
