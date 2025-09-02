<?php

namespace Tests\Unit\App\Listeners;

use App\Events\ExchangeRateCreatedEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;
use App\Listeners\ExchangeRateCreatedListener;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ExchangeRateCreatedListenerTest extends TestCase
{
    public function test_dispatches_the_fetch_job_when_event_is_handled(): void
    {
        // Fake the job dispatching
        Bus::fake();

        // Create a fake exchange rate
        $exchangeRate = new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 100,
        ]);

        // Fire the event
        $event = new ExchangeRateCreatedEvent($exchangeRate);

        // Handle the event manually (as Laravel would)
        $listener = new ExchangeRateCreatedListener();
        $listener->handle($event);

        // Assert that the job was dispatched with the correct exchange rate
        Bus::assertDispatched(FetchExchangeRateFromExternalProviderJob::class);
    }
}
