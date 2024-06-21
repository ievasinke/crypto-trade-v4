<?php

namespace App\Api;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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
        try {
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

            if ($response->getStatusCode() !== 200) {
                throw new HttpFailedRequestException(
                    'Failed to fetch currency data with CoinMarket API. Status code: ' . $response->getStatusCode()
                );
            }

            $currenciesData = $response->getBody()->getContents();
            $currencies = json_decode($currenciesData);
            return array_map('self::deserialize', $currencies->data);
        } catch (HttpFailedRequestException $e) {
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
            $object->quote->USD->price
        );
    }
}