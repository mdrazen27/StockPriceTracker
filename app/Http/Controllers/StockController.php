<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StockController extends Controller
{
    public function getStocksLatestPrice(Request $request): JsonResponse
    {
        $stocks = Stock::with('latestPrice');
        if ($request->stocks) {
            $stocks->whereIn('name', explode(',', $request->stocks));
        }

        return new JsonResponse($stocks->get());
    }

    public function getStockLatestPrice(string $name): JsonResponse
    {
        $stock = Cache::get($name);
        if (!$stock) {
            $stock = Stock::with('latestPrice')
                ->where('name', $name)
                ->first();
        }
        return new JsonResponse($stock);
    }
}
