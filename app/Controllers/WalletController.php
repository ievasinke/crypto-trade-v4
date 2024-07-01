<?php declare(strict_types=1);

namespace App\Controllers;

use App\Api\CoinmarketApiClient;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Response;
use App\Services\SqliteServices;
use App\Services\WalletServices;
use Exception;

class WalletController
{
    private WalletServices $walletServices;
    private UserRepository $userRepository;

    public function __construct()
    {
        $database = new SqliteServices();
        $client = new CoinmarketApiClient();
        $userRepository = new UserRepository($database);
        $walletRepository = new WalletRepository($database);
        $transactionRepository = new TransactionRepository($database);
        $this->walletServices = new WalletServices($client, $userRepository, $walletRepository, $transactionRepository);
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();
        $wallets = $this->walletServices->getWallets($userId);

        return new Response(
            'wallets/index',
            ['wallets' => $wallets]
        );
    }

    public function buy(string $symbol): ?Response // /currencies/{symbol}/buy
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();

        $quantity = (int)$_POST['quantity'] ?? null;

        if ($quantity === null) {
            return new Response(
                'error',
                ['message' => 'Invalid input.']
            );
        }

        try {
            $message = $this->walletServices->buyCurrency($userId, $symbol, $quantity);
            header("Location: /transactions", true, 301);
            return null;
        } catch (Exception $e) {
            return new Response(
                'error',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function sell(string $symbol): Response // /currencies/{symbol}/sell
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();

//        $symbol = (string)$_POST['symbol'] ?? null;
        $quantity = (int)$_POST['quantity'] ?? null;

        if ($quantity === null) {
            return new Response(
                'error',
                ['message' => 'Invalid input.']
            );
        }

        try {
            $message = $this->walletServices->sellCurrency($userId, $symbol, $quantity);
            return new Response(
                'success',
                ['message' => $message]
            );
        } catch (Exception $e) {
            return new Response(
                'error',
                ['message' => $e->getMessage()]
            );
        }
    }
}