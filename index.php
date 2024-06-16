<?php

require_once 'vendor/autoload.php';

use App\Api\CoingeckoApiClient;
use App\Models\User;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

const DB_FILE_NAME = "storage/database.sqlite";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$userName = (string)readline("Enter your username: ");
$userPassword = (string)readline("Enter your password: ");

$user = User::findByUsername($userName);

if (!$user) {
    exit("User not found.\n");
}
if (md5($userPassword) !== $user->getPassword()) {
    exit("Wrong password.\n");
}

while (true) {
    $outputTasks = new ConsoleOutput();
    $tableActivities = new Table($outputTasks);
    $tableActivities
        ->setHeaders(['Index', 'Action'])
        ->setRows([
            ['1', 'Show list of top currencies'],
            ['2', 'Wallet'],
            ['3', 'Buy'],
            ['4', 'Sell'],
            ['5', 'Display transaction list'],
            ['0', 'Exit'],
        ])
        ->render();
    $action = (int)readline("Enter the index of the action: ");

    if ($action === 0) {
        break;
    }

    switch ($action) {
        case 1: //Show list of top currencies
            $client = new CoingeckoApiClient();
            $currencies = $client->fetchCurrencyData();
            break;
        case 2: //Wallet

            break;
        case 3: //Buy

            break;
        case 4: //Sell

            break;
        case 5: //Display transaction list

            break;
        default:
            break;
    }
}
