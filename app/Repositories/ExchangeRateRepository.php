<?php

namespace App\Repositories;

use App\DataObjects\DTOInterface;
use App\Models\ExchangeRate;
use Carbon\Carbon;

class ExchangeRateRepository implements RepositoryInterface
{

    public function create(DTOInterface $dto): ExchangeRate
    {
        $id = (string) ExchangeRate::query()->insertGetId([
            'user_id' => $dto->getUserId(),
            'from_currency' => $dto->getFromCurrency(),
            'to_currency' => $dto->getToCurrency(),
            'amount' => $dto->getAmount(),
            'created_at' => Carbon::now(),
        ]);

        return $this->getById($id);
    }

    public function getById(string $id): ExchangeRate
    {
        return ExchangeRate::query()->find($id);
    }

    public function updateResult(string $id, float $result): ExchangeRate
    {
        ExchangeRate::query()->where('id', $id)->update(['result' => $result]);
        return $this->getById($id);
    }
}
