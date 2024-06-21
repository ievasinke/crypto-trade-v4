<?php declare(strict_types=1);

namespace App\Services;

use App\Api\ApiClient;
use App\Exceptions\HttpFailedRequestException;
use App\Models\User;
use App\Models\Wallet;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class WalletServices
{
    private ApiClient $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    private function getUserWallet(int $userId): array
    {
        $database = new SqliteServices();
        $results = $database->findByUserId('wallets', $userId);
        $wallets = [];
        foreach ($results as $result) {
            if ((float)$result['amount'] > 0) {
                $wallets[] = new Wallet(
                    $result['symbol'],
                    (float)$result['amount'],
                    (float)$result['average_price'],
                    $result['user_id']
                );
            }
        }
        return $wallets;
    }

    public function display(int $userId): void
    {
        $wallets = $this->getUserWallet($userId);

        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

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
        $total = number_format(User::findById($userId)->getBalance(), 2);
        echo "You have \$$total in your wallet\n";
    }

    public function buy(int $userId): void
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

        (new CurrencyServices($this->client))->displayList();
        $index = (int)readline("Enter the index of the crypto currency to buy: ") - 1;
        $quantity = (float)readline("Enter the quantity: ");
        $kind = 'buy';

        if (isset($currencies[$index])) {
            $currency = $currencies[$index];
            $price = $currency->getPrice();
            $symbol = $currency->getSymbol();
            $totalCost = $price * $quantity;

            $database = new SqliteServices();

            $user = User::findById($userId);
            $balance = $user->getBalance();

            if ($balance < $totalCost) {
                echo "You need \$" . number_format($totalCost, 2) . " but you have \$" . number_format($balance, 2) . ".\n";
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
            (new TransactionServices())
                ->log(
                    $userId,
                    $kind,
                    $symbol,
                    $price,
                    $quantity
                );
            echo "You bought $quantity $symbol for \$" . number_format($totalCost, 2) . ".\n";
            return;
        }
        echo "Invalid index.\n";
    }

    public function sell(int $userId): void
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

        $wallets = $this->getUserWallet($userId);

        if (count($wallets) === 0) {
            echo "There are no items in your wallet.\n";
            return;
        }

        $this->display($userId);

        $symbol = strtoupper((string)readline("Enter the symbol of the currency: "));
        $quantity = (float)readline("Enter the quantity to sell: ");
        $kind = 'sell';

        $currentPrices = [];
        foreach ($currencies as $currency) {
            $currentPrices[$currency->getSymbol()] = $currency->getPrice();
        }

        $wallet = null;

        foreach ($wallets as $item) {
            if ($item->getSymbol() === $symbol) {
                $wallet = $item;
                break;
            }
        }

        if ($wallet === null) {
            echo "There are no items in your wallet.\n";
        }

        if ($wallet->getAmount() < $quantity) {
            echo "You have $quantity of \$$symbol to sell.\n";
            return;
        }

        $currentPrice = $currentPrices[$symbol] ?? 0;
        $totalValue = $quantity * $currentPrice;

        $newAmount = $wallet->getAmount() - $quantity;
        $database = new SqliteServices();

        if ($newAmount > 0) {
            $database->update(
                'wallets',
                [
                    'amount' => $newAmount,
                ],
                [
                    'user_id' => $userId,
                    'symbol' => $symbol,
                ]
            );
        } else {
            $database->delete(
                'wallets',
                [
                    'user_id' => $userId,
                    'symbol' => $symbol,
                ]
            );
        }

        $user = User::findById($userId);
        $newBalance = $user->getBalance() + $totalValue;
        $user->updateBalance($newBalance);

        (new TransactionServices())
            ->log(
                $userId,
                $kind,
                $symbol,
                $currentPrice,
                $quantity
            );

        echo "You sold $symbol for \$" . number_format($totalValue, 2) . "\n";
    }
}