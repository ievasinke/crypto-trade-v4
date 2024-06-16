<?php

namespace App\Services;

use Medoo\Medoo;


class SqliteServices
{
    private Medoo $database;

    public function __construct()
    {
        $this->database = new Medoo([
            'type' => 'sqlite',
            'database' => DB_FILE_NAME
        ]);
    }

    public function create(string $tableName, array $values): void
    {
        $this->database->insert($tableName, $values);
    }

    public function findByUserId(string $tableName, int $id)
    {
        return $this->database->get($tableName, null, '*', ["user_id" => $id]);
    }

    public function findBy(string $tableName, string $column, string $value): array
    {
        return $this->database->select($tableName, '*', [$column => $value]) ?? [];
    }

}