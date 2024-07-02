<?php declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\TransactionServices;
use App\Response;

class TransactionController
{
    private TransactionServices $transactionServices;

    private UserRepository $userRepository;

    public function __construct(
        TransactionServices $transactionServices,
        UserRepository      $userRepository
    )
    {
        $this->transactionServices = $transactionServices;
        $this->userRepository = $userRepository;
    }

    public function index(): Response
    {
        $transactions = $this->transactionServices->getTransactions();
        return new Response(
            'transactions/index',
            ['transactions' => $transactions]
        );
    }
}