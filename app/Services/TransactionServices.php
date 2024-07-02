<?php declare(strict_types=1);

namespace App\Services;

use App\Repositories\TransactionRepository;

class TransactionServices
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getTransactions(): array
    {
        return $this->transactionRepository->getUserTransaction();
    }
}