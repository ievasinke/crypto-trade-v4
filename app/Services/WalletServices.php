<?php declare(strict_types=1);

namespace App\Services;

use App\Api\ApiClient;
use App\Api\CoinmarketApiClient;
use App\Exceptions\HttpFailedRequestException;
use App\Models\Wallet;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Exception;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class WalletServices
{
//    private ApiClient $client;
    private CoinmarketApiClient $client;
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;


    public function __construct(
        CoinmarketApiClient $client,
        UserRepository      $userRepository,
        WalletRepository    $walletRepository
    )
    {
        $this->client = $client;
        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
    }

    public function display(int $userId): void
    {
        $wallets = (new WalletRepository($this->database))->getUserWallets($userId);

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
        $total = number_format((float)$this->userRepository->findById($userId)->getBalance(), 2);
        echo "You have \$$total in your wallet\n";
    }

    public function buyCurrency(int $userId, string $symbol, int $quantity): string
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (Exception $e) {
            throw new Exception('Failed to fetch currency data: ' . $e->getMessage());
        }

        $currency = null;

        foreach ($currencies as $item) {
            if ($item->getSymbol() === strtoupper($symbol)) {
                $currency = $item;
                break;
            }
        }

        if (!$currency) {
            throw new Exception('Invalid currency symbol.');
        }

        $price = $currency->getPrice();
        $totalCost = $price * $quantity;

        $user = $this->userRepository->findById($userId);
        $balance = $user->getBalance();

        if ($balance < $totalCost) {
            throw new Exception('Your balance is too low.');
        }

        $existingWallets = $this->walletRepository->getUserWallets($userId);
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
            $this->walletRepository->updateWallet($userId, $symbol, $newAmount, $newAveragePrice);
        } else {
            $this->walletRepository->createWallet($symbol, $quantity, $price, $userId);
        }

        $newBalance = $balance - $totalCost;
        $this->userRepository->updateBalance($user, $newBalance);

        (new TransactionServices())->log($userId, 'buy', $symbol, $price, $quantity);

        return 'You have successfully bought the ' . $symbol . ' coins for $' . number_format($totalCost, 2);
    }

    public function sell(int $userId): void
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            $currencies = [];
        }

        $wallets = (new WalletRepository($this->database))->getUserWallets($userId);

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

        if ($newAmount > 0) {
            $this->database->update(
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
            $this->database->delete(
                'wallets',
                [
                    'user_id' => $userId,
                    'symbol' => $symbol,
                ]
            );
        }

        $user = $this->userRepository->findById($userId);
        $newBalance = $user->getBalance() + $totalValue;
        $this->userRepository->updateBalance($user, $newBalance);

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