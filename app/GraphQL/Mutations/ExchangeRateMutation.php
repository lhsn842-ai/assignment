<?php

namespace App\GraphQL\Mutations;

use App\DataObjects\CreateExchangeRateDto;
use App\Events\ExchangeRateCreatedEvent;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService\ExchangeRateServiceInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Http\Response;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ExchangeRateMutation
{
    public function __construct(private readonly ExchangeRateRepository $repository, private readonly ExchangeRateServiceInterface $service)
    {

    }

    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        try {
            $userId = auth()->id();

            $dto = new CreateExchangeRateDto(
                amount: $args['input']['amount'],
                fromCurrency: $args['input']['fromCurrency'],
                toCurrency: $args['input']['toCurrency'],
                userId: $userId,
            );

            $exchangeRate = $this->repository->create($dto);

            $cachedValue = $this->service->getCachedValue($exchangeRate);
            if (!$cachedValue) {
                event(new ExchangeRateCreatedEvent($exchangeRate));
            }

            return [
                'statusCode' => Response::HTTP_CREATED,
                'message' => 'Exchange rate created successfully.',
                'data' => $cachedValue ?? $exchangeRate,
            ];
        } catch (\Throwable $exception) {
            return [
                'statusCode' => Response::HTTP_EXPECTATION_FAILED,
                'message' => 'Exchange rate failed to create.',
                'data' => $exception->getMessage(),
            ];
        }
    }
}
