<?php

return [
    ['GET', '/', [App\Controllers\CurrencyController::class, 'index']],
    ['GET', '/currencies/{symbol}', [App\Controllers\CurrencyController::class, 'show']],
    ['POST', '/currency/search', [App\Controllers\CurrencyController::class, 'search']],
    ['POST', '/currencies/{symbol}/buy', [App\Controllers\WalletController::class, 'buy']],
    ['POST', '/currencies/{symbol}/sell', [App\Controllers\WalletController::class, 'sell']],
    ['GET', '/wallets', [App\Controllers\WalletController::class, 'index']],
    ['GET', '/transactions', [App\Controllers\TransactionController::class, 'index']]
];