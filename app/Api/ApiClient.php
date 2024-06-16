<?php

namespace App\Api;

use App\Models\Currency;

interface ApiClient
{
    /**
     * @return array<Currency>
     */
    public function fetchCurrencyData(): array;
}