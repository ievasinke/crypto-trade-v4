<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Wallet;

class WalletServices
{
    public function getUserWallet(int $userId): array
    {
        $database = new SqliteServices();
        $results = $database->findByUserId('wallets', $userId);
        $wallets = [];
        foreach ($results as $result) {
            $wallets[] = new Wallet(
                $result['symbol'],
                $result['amount'],
                $result['average_price'],
                $result['user_id']
            );
        }
        return $wallets;
    }
}