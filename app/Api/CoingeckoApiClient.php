<?php

namespace App\Api;

use App\Models\Currency;
use GuzzleHttp\Client;
use stdClass;

class CoingeckoApiClient implements ApiClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coingecko.com/api/v3/'
        ]);
    }

    public function fetchCurrencyData(): array
    {
        $response = $this->client->request(
            'GET',
            'coins/markets',
            [
                'query' => [
                    'vs_currency' => 'USD',
                    'per_page' => '20',
                ],
                'headers' => [
                    'accept' => 'application/json',
                    'x-cg-demo-api-key' => $_ENV['COIN_GECKO_API_KEY'],
                ],
            ]);
        $currenciesData = $response->getBody()->getContents();
        $currencies = json_decode($currenciesData);
        $currenciesList = array_map('self::deserialize', $currencies);
        return $currenciesList;
    }

    private function deserialize(stdClass $object): Currency
    {
        return new Currency(
            $object->name,
            strtoupper($object->symbol),
            $object->current_price
        );
    }
}