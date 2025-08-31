<?php

namespace App\DataObjects;

class CreateExchangeRateDto implements DTOInterface
{
    public function __construct(
        private readonly string $amount,
        private readonly string $fromCurrency,
        private readonly string $toCurrency,
        private readonly string $userId,
    ) {
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getFromCurrency(): string
    {
        return $this->fromCurrency;
    }

    public function getToCurrency(): string
    {
        return $this->toCurrency;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
