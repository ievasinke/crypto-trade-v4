<?php

namespace App\Controllers;

use App\Api\CoinmarketApiClient;
use App\Response;
use Exception;

class CurrencyController
{
    public function __construct()
    {
    }

    public function index(): Response // /index
    {
        try {
            $topCurrencies = (new CoinmarketApiClient())->fetchCurrencyData();
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
            $currency = (new CoinmarketApiClient())->searchCurrencyBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
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
            }
            $currency = (new CoinmarketApiClient())->searchCurrencyBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
            header("Location: /currencies/" . $symbol, true, 301);
            return null;
        } catch (Exception $e) {
            return new Response('error', ['message' => $e->getMessage()]);
        }
    }

}