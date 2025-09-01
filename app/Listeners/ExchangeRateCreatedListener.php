<?php

namespace App\Listeners;

use App\Events\ExchangeRateCreatedEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;

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
