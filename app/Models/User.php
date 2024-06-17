<?php

namespace App\Models;

use App\Services\SqliteServices;

class User
{
    private string $username;
    private string $password;
    private float $balance;
    private ?int $id;

    public function __construct(
        string $username,
        string $password,
        float  $balance = 1000.0,
        ?int   $id = null
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->balance = $balance;
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function add()
    {
        $database = new SqliteServices();
        $database->create(
            "users",
            [
                "username" => $this->username,
                "password" => $this->password,
                "balance" => $this->balance
            ],
        );
    }

    public function login(string $password): bool
    {
        return md5($password) === $this->password;
    }

    public function updateBalance(float $newBalance): void
    {
        $database = new SqliteServices();
        $database->update(
            "users",
            ["balance" => $newBalance],
            ["id" => $this->id]
        );
        $this->balance = $newBalance;
    }

    public static function findByUsername(string $username): ?User
    {
        $database = new SqliteServices();
        $result = $database->findBy("users", "username", $username);

        if (count($result) === 1) {
            $userdata = reset($result);
            return new User($userdata['username'], $userdata['password'], $userdata['balance'], $userdata['id']);
        }
        return null;
    }

    public static function findById(int $id): ?User
    {
        $database = new SqliteServices();
        $result = $database->findBy("users", "id", (string)$id);

        if (count($result) === 1) {
            $userdata = reset($result);
            return new User($userdata['username'], $userdata['password'], $userdata['balance'], $userdata['id']);
        }
        return null;
    }


}