<?php

require 'vendor/autoload.php';

use App\Models\User;
use Medoo\Medoo;

$database = new Medoo([
    'type' => 'sqlite',
    'database' => 'storage/database.sqlite'
]);

$database->query("CREATE TABLE IF NOT EXISTS users (
id INTEGER PRIMARY KEY AUTOINCREMENT,
username VARCHAR(32) NOT NULL,
password VARCHAR(32) NOT NULL,
balance REAL NOT NULL
)");

$database->query("CREATE TABLE IF NOT EXISTS wallets (
id INTEGER PRIMARY KEY AUTOINCREMENT,
symbol TEXT NOT NULL,
amount REAL NOT NULL,
average_price REAL NOT NULL,
user_id INTEGER NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id)
)");

$database->query("CREATE TABLE IF NOT EXISTS transactions (
id INTEGER PRIMARY KEY AUTOINCREMENT,
user_id INTEGER NOT NULL,
kind TEXT NOT NULL,
name TEXT NOT NULL,
symbol TEXT NOT NULL,
price REAL NOT NULL,
quantity REAL NOT NULL,
created_at TEXT NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id)
)");

(new User("admin", md5('admin'), 1000))->add();
(new User("Customer", md5('654321'), 1000))->add();

echo "Database schema initialized.\n";