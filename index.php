<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Api\CoingeckoApiClient;
use App\Api\CoinmarketApiClient;
use App\Services\CurrencyServices;
use App\Services\TransactionServices;
use App\Services\WalletServices;
use App\Controllers\CurrencyController;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;


use App\Repositories\UserRepository;
use App\Services\SqliteServices;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

//$client = new CoinmarketApiClient();
//$currencyServices = new CurrencyServices($client);
//$walletServices = new WalletServices($client, $database, $userRepository);
//$transactionServices = new TransactionServices();
//$database = new SqliteServices();
//$userRepository = new UserRepository($database);
$user = (new UserRepository(new SqliteServices()))->findByUsername('Customer');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$loader = new FilesystemLoader(__DIR__ . '/app/Templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/index', [App\Controllers\CurrencyController::class, 'index']);
    $r->addRoute('GET', '/currencies/{symbol}', [App\Controllers\CurrencyController::class, 'show']);
//    $r->addRoute('GET/POST', '/', [App\Controllers\CurrencyController::class, 'buy']);
//    $r->addRoute('GET/POST', '/wallets', [App\Controllers\CurrencyController::class, 'sell']);
//    $r->addRoute('GET/POST', '/transactions', [App\Controllers\CurrencyController::class, 'change']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$controller, $method] = $handler;
        // ... call $handler with $vars
        $items = (new $controller($twig))->$method(...array_values($vars));
        echo $items;
        break;
}

//while (true) {
//    $outputTasks = new ConsoleOutput();
//    $tableActivities = new Table($outputTasks);
//    $tableActivities
//        ->setHeaders(['Index', 'Action'])
//        ->setRows([
//            ['1', 'Show list of top currencies'],
//            ['2', 'Wallet'],
//            ['3', 'Buy'],
//            ['4', 'Sell'],
//            ['5', 'Display transaction list'],
//            ['0', 'Exit'],
//        ])
//        ->render();
//    $action = (int)readline("Enter the index of the action: ");
//
//    if ($action === 0) {
//        break;
//    }
//
//    switch ($action) {
//        case 2: //Wallet
//            $walletServices->display($user->getId());
//            break;
//        case 3: //Buy
//            $walletServices->buy($user->getId());
//            break;
//        case 4: //Sell
//            $walletServices->sell($user->getId());
//            break;
//        case 5: //Display transaction list
//            $transactionServices->display($user->getId());
//            break;
//        default:
//            break;
//    }
//}
