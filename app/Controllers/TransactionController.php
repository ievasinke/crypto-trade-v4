<?php declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\TransactionServices;
use App\Response;
use App\Services\SqliteServices;

class TransactionController
{
    private SqliteServices $database;
    private TransactionRepository $transactionRepository;
    private TransactionServices $transactionServices;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->database = new SqliteServices();
        $this->transactionRepository = new TransactionRepository($this->database);
        $this->transactionServices = new TransactionServices($this->transactionRepository);
        $this->userRepository = new UserRepository($this->database);
    }

    public function index(): Response
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();

        $transactions = $this->transactionServices->getTransactions($userId);
        return new Response(
            'transactions/index',
            ['transactions' => $transactions]
        );
    }
}