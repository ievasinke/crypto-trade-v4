<?php

use App\Api\ApiClient;
use App\Api\CoinmarketApiClient;
use App\Controllers\TransactionController;
use App\Controllers\WalletController;
use App\Repositories\CurrencyRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\SqliteServices;
use App\Services\CurrencyServices;
use App\Services\TransactionServices;
use App\Services\WalletServices;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    ApiClient::class => DI\autowire(
        CoinmarketApiClient::class
    ),
    CurrencyRepository::class => DI\autowire()->constructor(DI\get(
        ApiClient::class
    )),
    CurrencyServices::class => DI\autowire()->constructor(DI\get(
        CurrencyRepository::class
    )),
    SqliteServices::class => DI\autowire(),
    TransactionController::class => DI\autowire()->constructor(DI\get(
        TransactionServices::class,
        UserRepository::class
    )),
    TransactionRepository::class => DI\autowire()->constructor(DI\get(
        SqliteServices::class,
        UserRepository::class
    )),
    TransactionServices::class => DI\autowire()->constructor(DI\get(
        TransactionRepository::class
    )),
    UserRepository::class => DI\autowire()->constructor(DI\get(
        SqliteServices::class
    )),
    WalletController::class => DI\autowire(),
    WalletRepository::class => DI\autowire()->constructor(DI\get(
        SqliteServices::class
    )),
    WalletServices::class => DI\autowire()
]);

return $containerBuilder->build();