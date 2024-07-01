<?php declare(strict_types=1);

namespace App\Services;

use App\Api\CoinmarketApiClient;
use App\Exceptions\HttpFailedRequestException;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Exception;

class WalletServices
{
    private CoinmarketApiClient $client;
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;


    public function __construct(
        CoinmarketApiClient   $client,
        UserRepository        $userRepository,
        WalletRepository      $walletRepository,
        TransactionRepository $transactionRepository
    )
    {
        $this->client = $client;
        $this->userRepository = $userRepository;
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getWallets(int $userId): array
    {
        return $this->walletRepository->getUserWallets($userId);
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

        $this->transactionRepository->log($userId, 'buy', $symbol, $price, $quantity);

        return 'You have successfully bought the ' . $symbol . ' coins for $' . number_format($totalCost, 2);
    }

    public function sellCurrency(int $userId, string $symbol, float $quantity): string
    {
        try {
            $currencies = $this->client->fetchCurrencyData();
        } catch (HttpFailedRequestException $e) {
            throw new Exception('Failed to fetch currency data: ' . $e->getMessage());
        }

        $existingWallets = $this->walletRepository->getUserWallets($userId);
        $existingWallet = null;

        foreach ($existingWallets as $wallet) {
            if ($wallet->getSymbol() === $symbol) {
                $existingWallet = $wallet;
            }
        }

        if (!$existingWallet || $existingWallet->getAmount() < $quantity) {
            throw new Exception('Insufficient amount in your wallet.');
        }

        $currency = null;
        foreach ($currencies as $item) {
            if ($item->getSymbol() === strtoupper($symbol)) {
                $currency = $item;
                break;
            }
        }

        $currentPrice = $currency->getPrice() ?? 0;
        $totalValue = $quantity * $currentPrice;
        $newAmount = $existingWallet->getAmount() - $quantity;

        if ($newAmount > 0) {
            $this->walletRepository->updateWallet($userId, $symbol, $newAmount, $existingWallet->getAveragePrice());
        } else {
            $this->walletRepository->deleteWallet($userId, $symbol);
        }

        $user = $this->userRepository->findById($userId);
        $newBalance = $user->getBalance() + $totalValue;
        $this->userRepository->updateBalance($user, $newBalance);

        $this->transactionRepository->log($userId, 'sell', $symbol, $currentPrice, $quantity);

        return 'You have successfully sold ' . $symbol . ' coins for $' . number_format($totalValue, 2);
    }
}