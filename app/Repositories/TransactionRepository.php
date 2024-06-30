<?php declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use App\Services\SqliteServices;
use Carbon\Carbon;

class TransactionRepository
{
    private SqliteServices $database;
    private UserRepository $userRepository;

    public function __construct(SqliteServices $database)
    {
        $this->database = $database;
        $this->userRepository = new UserRepository($this->database);
    }

    public function getUserTransaction(): array
    {
        $user = $this->userRepository->findByUsername('Customer'); //TODO remove
        $userId = $user->getId();

        $results = (new SqliteServices())->findByUserId('transactions', $userId);
        $transactions = [];
        foreach ($results as $result) {
            $transactions[] = new Transaction(
                (int)$result['user_id'],
                $result['kind'],
                $result['symbol'],
                (float)$result['price'],
                (float)$result['quantity'],
                Carbon::parse($result['created_at'])
            );
        }
        return $transactions;
    }

    public function log(
        int    $userId,
        string $kind,
        string $symbol,
        float  $price,
        float  $quantity
    ): void
    {
        $this->database->create(
            'transactions',
            [
                'user_id' => $userId,
                'kind' => $kind,
                'symbol' => $symbol,
                'price' => $price,
                'quantity' => $quantity,
                'created_at' => Carbon::now()->toIso8601String(),
            ]
        );
    }
}