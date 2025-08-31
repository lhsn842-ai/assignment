<?php

namespace App\Repositories;

use App\DataObjects\DTOInterface;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use MongoDB\Laravel\Eloquent\Model;

class ExchangeRateRepository implements RepositoryInterface
{

    public function create(DTOInterface $dto): Model
    {
        $id = ExchangeRate::query()->insertGetId([
            'user_id' => $dto->getUserId(),
            'from_currency' => $dto->getFromCurrency(),
            'to_currency' => $dto->getToCurrency(),
            'amount' => $dto->getAmount(),
            'created_at' => Carbon::now(),
        ]);

        return $this->getById($id);
    }

    public function getById(string $id): Model
    {
        return ExchangeRate::query()->find($id);
    }

    public function updateResult(string $id, string $result): Model
    {
        ExchangeRate::query()->where('id', $id)->update(['result' => $result]);
        return $this->getById($id);
    }
}
