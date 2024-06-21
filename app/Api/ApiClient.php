<?php

namespace App\Api;

use App\Exceptions\HttpFailedRequestException;
use App\Models\Currency;

interface ApiClient
{
    /**
     * @return array<Currency>
     * @throws HttpFailedRequestException
     */
    public function fetchCurrencyData(): array;
}