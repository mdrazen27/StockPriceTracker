<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/stocks-latest-price', [StockController::class, 'getStocksLatestPrice']);
Route::get('/stock-latest-price/{name}', [StockController::class, 'getStockLatestPrice']);
Route::get('/stock-price-difference/{name}', [StockController::class, 'getStockPriceDifference']);
Route::apiResource('/stocks', StockController::class);

