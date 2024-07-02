<?php declare(strict_types=1);

namespace App\Repositories;

use App\Api\ApiClient;
use App\Models\Currency;

class CurrencyRepository
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function fetchAll(): array
    {
        return $this->client->fetchCurrencyData();
    }

    public function findBySymbol(string $symbol): ?Currency
    {
        return $this->client->searchCurrencyBySymbol($symbol);
    }
}