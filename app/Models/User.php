<?php

namespace App\Models;

use App\Services\SqliteServices;

class User
{
    private string $username;
    private string $password;
    private ?int $id;

    public function __construct(
        string $username,
        string $password,
        ?int   $id = null
    )
    {
        $this->username = $username;
        $this->password = $password;
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
                "password" => $this->password
            ],
        );
    }

    public static function findByUsername(string $username): ?User
    {
        $database = new SqliteServices();
        $result = $database->findBy("users", "username", $username);

        if (count($result) === 1) {
            $userdata = reset($result);
            return new User($userdata['username'], $userdata['password'], $userdata['id']);
        }
        return null;
    }
}