<?php declare(strict_types=1);

namespace App\Controllers;

use App\Services\TransactionServices;
use App\Response;

class TransactionController
{
    private TransactionServices $transactionServices;

    public function __construct(TransactionServices $transactionServices)
    {
        $this->transactionServices = $transactionServices;
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