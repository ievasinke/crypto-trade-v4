<?php

namespace App\Api;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
        try {
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

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to fetch currency data with CoinGecko API.',
                    $response->getStatusCode()
                );
            }

            $currenciesData = $response->getBody()->getContents();
            $currencies = json_decode($currenciesData);
            return array_map('self::deserialize', $currencies);
        } catch (RequestException $e) {
            throw new HttpFailedRequestException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
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