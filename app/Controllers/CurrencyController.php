<?php

namespace App\Controllers;

use App\Api\CoinmarketApiClient;
use Exception;
use Twig\Environment;

class CurrencyController
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): string // /index
    {
        try {
            $topCurrencies = (new CoinmarketApiClient())->fetchCurrencyData();
            return $this->twig->render(
                'index.html.twig',
                ['currencies' => $topCurrencies]
            );
        } catch (Exception $e) {
            return $this->twig->render(
                'error.html.twig',
                ['message' => 'Failed to fetch currencies']
            );
        }
    }

    public function show(string $symbol): string // /currencies/{symbol}
    {
        try {
            $currency = (new CoinmarketApiClient())->searchCurrencyBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
            return $this->twig->render('show.html.twig', ['currency' => $currency]);
        } catch (Exception $e) {
            return $this->twig->render('error.html.twig', ['message' => $e->getMessage()]);
        }
    }

    public function search(): ?string // /currency/search
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
            return $this->twig->render('error.html.twig', ['message' => $e->getMessage()]);
        }
    }

}