<?php

namespace Tests\Unit\App\Jobs;

use App\Events\ExchangeRateResultReadyEvent;
use App\Jobs\FetchExchangeRateFromExternalProviderJob;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use App\ValueObjects\SingleCurrencyExchangeRateVOInterface;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class FetchExchangeRateFromExternalProviderJobTest extends TestCase
{
    public function test_job_executes_successfully_and_dispatches_event()
    {
        Event::fake();

        $exchangeRate = new ExchangeRate([
            'amount' => 100,
            'user_id' => 'user-123',
        ]);
        $exchangeRate->id = 'test1123';
        $quoteMock = Mockery::mock(SingleCurrencyExchangeRateVOInterface::class);
        $quoteMock->shouldReceive('getQuote')->andReturn(11.1);

        $serviceMock = Mockery::mock(ExchangeRateServiceInterface::class);
        $serviceMock->shouldReceive('exchangeSingleCurrency')
            ->with($exchangeRate)
            ->andReturn($quoteMock);

        $repositoryMock = Mockery::mock(ExchangeRateRepository::class);
        $repositoryMock->shouldReceive('updateResult')
            ->once()
            ->with($exchangeRate->id, 11.1 * $exchangeRate->amount);

        $job = new FetchExchangeRateFromExternalProviderJob($exchangeRate);

        $job->handle($serviceMock, $repositoryMock);

        Event::assertDispatched(ExchangeRateResultReadyEvent::class, function ($event) use ($exchangeRate) {
            return $event->exchangeRate->id === $exchangeRate->id;
        });
    }

    public function test_job_handles_service_exception()
    {
        $exchangeRate = new ExchangeRate([
            'id' => 'test-id',
            'amount' => 100,
            'user_id' => 'user-123',
            'result' => null,
        ]);

        $serviceMock = Mockery::mock(ExchangeRateServiceInterface::class);
        $serviceMock->shouldReceive('exchangeSingleCurrency')
            ->andThrow(new \Exception('API failure'));

        $repositoryMock = Mockery::mock(ExchangeRateRepository::class);
        $repositoryMock->shouldIgnoreMissing();

        $job = new FetchExchangeRateFromExternalProviderJob($exchangeRate);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API failure');

        $job->handle($serviceMock, $repositoryMock);
    }
}
