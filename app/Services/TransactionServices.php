<?php

namespace App\Services;

use Carbon\Carbon;

class TransactionServices
{
    public function log(
        int    $userId,
        string $kind,
        string $symbol,
        float  $price,
        float  $quantity
    ): void
    {
        $database = new SqliteServices();
        $database->create(
            'transactions',
            [
                'user_id' => $userId,
                'kind' => $kind,
                'symbol' => $symbol,
                'price' => $price,
                'quantity' => $quantity,
                'created_at' => Carbon::now()->toIso8601String(),
            ]);
    }


}