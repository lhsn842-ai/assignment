<?php

namespace App\ValueObjects;

readonly class SwopSingleCurrencyExchangeRateVO implements SingleCurrencyExchangeRateVOInterface
{
    public function __construct(
        private readonly string $quote,
        private readonly string $date,
    )
    {
    }

    public function getQuote(): string
    {
        return $this->quote;
    }

    public function getDate(): string
    {
        return $this->date;
    }
}
