<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Services\CurrencyServices;
use App\Services\WalletServices;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$userName = (string)readline("Enter your username: ");
$userPassword = (string)readline("Enter your password: ");

$user = User::findByUsername($userName);

if (!$user) {
    exit("User not found.\n");
}

if ($user->login($userPassword) === false) {
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
            (new CurrencyServices())->displayList();
            break;
        case 2: //Wallet
            (new WalletServices())->display($user->getId());
            break;
        case 3: //Buy
            (new WalletServices())->buy($user->getId());
            break;
        case 4: //Sell

            break;
        case 5: //Display transaction list

            break;
        default:
            break;
    }
}
