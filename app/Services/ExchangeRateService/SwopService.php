<?php

namespace App\Services\ExchangeRateService;

use App\Models\ExchangeRate;
use App\ValueObjects\SingleCurrencyExchangeRateVOInterface;
use App\ValueObjects\SwopSingleCurrencyExchangeRateVO;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class SwopService implements ExchangeRateServiceInterface
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('SWOP_API_KEY');
        $this->apiUrl = 'https://swop.cx/rest';
    }

    public function exchangeSingleCurrency(ExchangeRate $exchangeRate): SingleCurrencyExchangeRateVOInterface
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'ApiKey ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->apiUrl . '/rates/' . $exchangeRate->from_currency . '/' . $exchangeRate->to_currency);

            if ($response->failed()) {
                throw new Exception('API request failed: ' . $response->body());
            }

            $responseAsArray = json_decode($response->body(), true);
            return new SwopSingleCurrencyExchangeRateVO($responseAsArray['quote'], $responseAsArray['date']);
        } catch (Throwable $e) {
            throw new Exception('Error fetching conversion: ' . $e->getMessage());
        }
    }
}
