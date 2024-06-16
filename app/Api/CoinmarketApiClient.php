<?php

namespace App\Api;

use App\Models\Currency;
use GuzzleHttp\Client;
use stdClass;

class CoinmarketApiClient implements ApiClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/'
        ]);
    }

    public function fetchCurrencyData(): array
    {
        $response = $this->client->request(
            'GET',
            'cryptocurrency/listings/latest',
            [
                'query' => [
                    'start' => '1',
                    'limit' => '20',
                    'convert' => 'USD'
                ],
                'headers' => [
                    'Accepts' => 'application/json',
                    'X-CMC_PRO_API_KEY' => $_ENV['CRYPTO_API_KEY']
                ],
            ]);
        $currenciesData = $response->getBody()->getContents();
        $currencies = json_decode($currenciesData);
        $currenciesList = array_map('self::deserialize', $currencies->data);
        return $currenciesList;
    }

    private function deserialize(stdClass $object): Currency
    {
        return new Currency(
            $object->name,
            strtoupper($object->symbol),
            $object->quote->USD->price
        );
    }
}