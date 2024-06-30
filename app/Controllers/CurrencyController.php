<?php

namespace App\Controllers;

use App\Api\CoinmarketApiClient;
use App\Services\CurrencyServices;
use App\Response;
use Exception;

class CurrencyController
{
    private CurrencyServices $currencyServices;

    public function __construct()
    {
        $client = new CoinmarketApiClient();
        $this->currencyServices = new CurrencyServices($client);
    }

    public function index(): Response // /index
    {
        try {
            $topCurrencies = $this->currencyServices->fetchCurrencies();
            return new Response(
                'currencies/index',
                ['currencies' => $topCurrencies]
            );
        } catch (Exception $e) {
            return new Response(
                'error',
                ['message' => 'Failed to fetch currencies']
            );
        }
    }

    public function show(string $symbol): Response // /currencies/{symbol}
    {
        try {
            $currency = $this->currencyServices->searchCurrency($symbol);
            return new Response('currencies/show', ['currency' => $currency]);
        } catch (Exception $e) {
            return new Response('error', ['message' => $e->getMessage()]);
        }
    }

    public function search(): ?Response // /currency/search
    {
        try {
            if (isset($_POST['symbol'])) {
                $symbol = $_POST['symbol'];
            } else {
                throw new Exception('Symbol not provided');
            }
//            $currency = $this->currencyServices->searchCurrency($symbol);
            header("Location: /currencies/" . $symbol, true, 301);
            return null;
        } catch (Exception $e) {
            return new Response('error', ['message' => $e->getMessage()]);
        }
    }
}