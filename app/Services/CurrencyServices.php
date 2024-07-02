<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use Exception;

class CurrencyServices
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function fetchCurrencies(): array
    {
        try {
            return $this->currencyRepository->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Failed to fetch currencies', 0, $e);
        }
    }

    public function searchCurrency(string $symbol): Currency
    {
        try {
            $currency = $this->currencyRepository->findBySymbol($symbol);
            if ($currency === null) {
                throw new Exception('Currency not found for symbol ' . $symbol);
            }
            return $currency;
        } catch (Exception $e) {
            throw new Exception('Failed to search currency', 0, $e);
        }
    }
}