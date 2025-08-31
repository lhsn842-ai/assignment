<?php

namespace Tests\Unit\App\GraphQl\Mutations;

use App\Events\ExchangeRateCreatedEvent;
use App\GraphQL\Mutations\ExchangeRateMutation;
use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
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

        $repository = Mockery::mock(ExchangeRateRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturn(new ExchangeRate([
                'id' => 1,
                'from_currency' => 'USD',
                'to_currency' => 'EUR',
                'amount' => 100,
                'status' => 'pending',
            ]));

        $resolver = new ExchangeRateMutation($repository);

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
        $repository = Mockery::mock(ExchangeRateRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $resolver = new ExchangeRateMutation($repository);

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
