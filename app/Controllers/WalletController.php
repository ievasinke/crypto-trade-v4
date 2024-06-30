<?php declare(strict_types=1);

namespace App\Controllers;

use App\Api\CoinmarketApiClient;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Response;
use App\Services\SqliteServices;
use App\Services\WalletServices;
use Exception;
use Twig\Environment;

class WalletController
{
    private CoinmarketApiClient $client;
    private SqliteServices $database;
    private UserRepository $userRepository;
    private WalletRepository $walletRepository;
    private WalletServices $walletServices;

    public function __construct()
    {
        $this->client = new CoinmarketApiClient();
        $this->database = new SqliteServices();
        $this->userRepository = new UserRepository($this->database);
        $this->walletRepository = new WalletRepository($this->database);
        $this->walletServices = new WalletServices($this->client, $this->userRepository, $this->walletRepository);
    }

    public function buy(): Response // /currency/buy
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();

        $symbol = (string)$_POST['symbol'] ?? null;
        $quantity = (int)$_POST['quantity'] ?? null;

        if ($symbol === null || $quantity === null) {
            return new Response(
                'error',
                ['message' => 'Invalid input.']
            );
        }

        try {
            $message = $this->walletServices->buyCurrency($userId, $symbol, $quantity);
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