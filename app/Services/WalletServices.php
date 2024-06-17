<?php declare(strict_types=1);

namespace App\Services;

use App\Api\CoingeckoApiClient;
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
                (float)$result['amount'],
                (float)$result['average_price'],
                $result['user_id']
            );
        }
        return $wallets;
    }

    public function buy(int $userId): void
    {
        $client = new CoingeckoApiClient();
        $currencies = $client->fetchCurrencyData();
        (new CurrencyServices())->displayList();
        $index = (int)readline("Enter the index of the crypto currency to buy: ") - 1;
        $quantity = (float)readline("Enter the quantity: ");

        if (isset($currencies[$index])) {
            $currency = $currencies[$index];
            $price = $currency->getPrice();
            $symbol = $currency->getSymbol();

            $database = new SqliteServices();
            $existingWallets = $this->getUserWallet($userId);
            $existingWallet = null;

            foreach ($existingWallets as $wallet) {
                if ($wallet->getSymbol() === $symbol) {
                    $existingWallet = $wallet;
                    break;
                }
            }
            if ($existingWallet) {
                $newAmount = $existingWallet->getAmount() + $quantity;
                $newAveragePrice = ($price * $quantity + $existingWallet->getAveragePrice() * $existingWallet->getAmount()) / $newAmount;

                $database->update(
                    'wallets',
                    [
                        'amount' => $newAmount,
                        'average_price' => $newAveragePrice,
                    ],
                    [
                        'user_id' => $userId,
                        'symbol' => $symbol,
                    ]
                );
            } else {
                $database->create(
                    'wallets',
                    [
                        'symbol' => $symbol,
                        'amount' => $quantity,
                        'average_price' => $price,
                        'user_id' => $userId,
                    ]);
            }
            echo "You bought $quantity $symbol at $price each.\n";
            return;
        }
        echo "Invalid index.\n";
    }
}