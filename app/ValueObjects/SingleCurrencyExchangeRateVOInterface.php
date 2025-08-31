<?php

namespace App\ValueObjects;

interface SingleCurrencyExchangeRateVOInterface
{
    public function getQuote(): string;
}
