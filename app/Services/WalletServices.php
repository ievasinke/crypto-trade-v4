<?php declare(strict_types=1);

namespace App\Services;

use App\Api\CoingeckoApiClient;
use App\Models\User;
use App\Models\Wallet;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class WalletServices
{
    private function getUserWallet(int $userId): array
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

    public function display(int $userId): void
    {
        $wallets = $this->getUserWallet($userId);
        $currencies = (new CoingeckoApiClient())->fetchCurrencyData();

        $currentPrices = [];
        foreach ($currencies as $currency) {
            $currentPrices[$currency->getSymbol()] = $currency->getPrice();
        }

        $outputCrypto = new ConsoleOutput();
        $tableCurrencies = new Table($outputCrypto);
        $tableCurrencies
            ->setHeaders(['Symbol', 'Amount', 'Average price', 'Profitability']);
        $tableCurrencies
            ->setRows(array_map(function (Wallet $wallet) use ($currentPrices): array {
                $symbol = $wallet->getSymbol();
                $currentPrice = $currentPrices[$symbol] ?? 0;

                $profitability = $wallet->calculateProfitability($currentPrice);

                return [
                    $symbol,
                    $wallet->getAmount(),
                    new TableCell(
                        number_format($wallet->getAveragePrice(), 2),
                        ['style' => new TableCellStyle(['align' => 'right',])]
                    ),
                    new TableCell(
                        number_format($profitability, 2) . "%",
                        ['style' => new TableCellStyle(['align' => 'center',])]
                    ),
                ];
            }, $wallets));
        $tableCurrencies->setStyle('box-double');
        $tableCurrencies->render();
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
            $totalCost = $price * $quantity;

            $database = new SqliteServices();

            $user = User::findById($userId);
            $balance = $user->getBalance();

            if ($balance < $totalCost) {
                echo "You need \$$totalCost but you have \$$balance.\n";
                return;
            }

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
                $newAveragePrice = ($totalCost + $existingWallet->getAveragePrice() * $existingWallet->getAmount()) / $newAmount;

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

            $newBalance = $balance - $totalCost;
            $user->updateBalance($newBalance);
            echo "You bought $quantity $symbol at $price each.\n";
            return;
        }
        echo "Invalid index.\n";
    }
}