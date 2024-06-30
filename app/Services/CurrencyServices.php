<?php declare(strict_types=1);

namespace App\Services;

use App\Api\CoinmarketApiClient;
use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use Exception;

class CurrencyServices
{
    private CoinmarketApiClient $client;

    public function __construct(CoinmarketApiClient $client)
    {
        $this->client = $client;
    }

    public function fetchCurrencies(): array
    {
        try {
            return $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            throw new Exception('Failed to fetch currencies', 0, $e);
        }
    }

    public function searchCurrency(string $symbol): Currency
    {
        try {
            $currency = $this->client->searchCurrencyBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
            return $currency;
        } catch (HttpFailedRequestException $e) {
            throw new Exception('Failed to search currency', 0, $e);
        }
    }
}