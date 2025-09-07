<?php

namespace Tests\Unit\App\GraphQl\Mutations;

use App\Events\ExchangeRateCreatedEvent;
use App\GraphQL\Mutations\ExchangeRateMutation;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Mockery;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Tests\TestCase;

class ExchangeRateMutationTest extends TestCase
{
    public function test_creates_exchange_rate_and_dispatches_event(): void
    {
        Event::fake();
        $user = User::factory()->make(['id' => 1]);
        $this->be($user);

        $repository = Mockery::mock(ExchangeRateRepository::class);
        $exchangeRate = new ExchangeRate([
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 100,
            'status' => 'pending',
        ]);
        $exchangeRate->id = '507f1f77bcf86cd799439011';
        $repository->shouldReceive('create')
            ->once()
            ->andReturn($exchangeRate);

        $exchangeRateService = Mockery::mock(ExchangeRateServiceInterface::class);
        $exchangeRateService->shouldReceive('getCachedValue')->andReturn(null);
        $resolver = new ExchangeRateMutation($repository, $exchangeRateService);

        $args = [
            'input' => [
                'amount' => 100,
                'fromCurrency' => 'EUR',
                'toCurrency' => 'USD',
            ],
        ];

        $result = $resolver->create(
            null,
            $args,
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );

        $this->assertEquals(Response::HTTP_CREATED, $result['statusCode']);
        $this->assertEquals('Exchange rate created successfully.', $result['message']);
        $this->assertInstanceOf(ExchangeRate::class, $result['data']);

         Event::assertDispatched(ExchangeRateCreatedEvent::class);
    }

    public function test_returns_error_when_creation_fails(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $this->be($user);

        $repository = Mockery::mock(ExchangeRateRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));
        $exchangeRateService = Mockery::mock(ExchangeRateServiceInterface::class);

        $resolver = new ExchangeRateMutation($repository, $exchangeRateService);

        $args = [
            'input' => [
                'amount' => 100,
                'fromCurrency' => 'USD',
                'toCurrency' => 'EUR',
            ],
        ];

        $result = $resolver->create(
            null,
            $args,
            Mockery::mock(GraphQLContext::class),
            Mockery::mock(ResolveInfo::class)
        );

        $this->assertEquals(Response::HTTP_EXPECTATION_FAILED, $result['statusCode']);
        $this->assertEquals('Exchange rate failed to create.', $result['message']);
        $this->assertEquals('Database error', $result['data']);
    }
}
