<?php

require 'vendor/autoload.php';

use Medoo\Medoo;

$database = new Medoo([
    'type' => 'sqlite',
    'database' => 'storage/database.sqlite'
]);

$database->query("CREATE TABLE IF NOT EXISTS users (
id INTEGER PRIMARY KEY AUTOINCREMENT,
username VARCHAR(32) NOT NULL,
password VARCHAR(32) NOT NULL,
balance FLOAT NOT NULL
)");

$database->query("CREATE TABLE IF NOT EXISTS wallets (
id INTEGER PRIMARY KEY AUTOINCREMENT,
symbol VARCHAR(255) NOT NULL,
amount FLOAT NOT NULL,
average_price FLOAT NOT NULL,
user_id INTEGER NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id)
)");

$database->query("CREATE TABLE IF NOT EXISTS transactions (
id INTEGER PRIMARY KEY AUTOINCREMENT,
user_id INTEGER NOT NULL,
kind VARCHAR(255) NOT NULL,
symbol VARCHAR(255) NOT NULL,
price FLOAT NOT NULL,
quantity FLOAT NOT NULL,
created_at DATETIME NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id)
)");

echo "Database schema initialized.\n";