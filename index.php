<?php declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Repositories\UserRepository;
use App\Services\SqliteServices;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$user = (new UserRepository(new SqliteServices()))->findByUsername('Customer');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $routes = include('routes.php');
    foreach ($routes as $route) {
        [$method, $path, $controller] = $route;
        $r->addRoute($method, $path, $controller);
    }
//    $r->addRoute('POST', '/wallets', [App\Controllers\CurrencyController::class, 'sell']);
//    $r->addRoute('GET', '/transactions', [App\Controllers\CurrencyController::class, 'change']);
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
        $controllerInstance = new $controller($twig);
        $items = $controllerInstance->$method(...array_values($vars));
        echo $items;
        break;
}
