<?php

namespace App\Repositories;

use App\DataObjects\DTOInterface;
use MongoDB\Laravel\Eloquent\Model;

interface RepositoryInterface
{
    public function create(DTOInterface $dto): Model;
    function getById(string $id): ?Model;
}
