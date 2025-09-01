<?php

namespace App\Repositories;

use App\DataObjects\DTOInterface;
use App\Models\ExchangeRate;

interface RepositoryInterface
{
    public function create(DTOInterface $dto): ExchangeRate;
    function getById(string $id): ?ExchangeRate;
}
