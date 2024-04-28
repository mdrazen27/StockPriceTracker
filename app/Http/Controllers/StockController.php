<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
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

    public function index(): JsonResponse
    {
        return new JsonResponse(Stock::all());
    }

    public function store(StoreStockRequest $request): JsonResponse
    {
        $stock = Stock::create($request->validated());
        return new JsonResponse($stock);
    }

    public function show(Stock $stock): JsonResponse
    {
        return new JsonResponse($stock);
    }

    public function update(UpdateStockRequest $request, Stock $stock): JsonResponse
    {
        $stock->update($request->validated());
        return new JsonResponse($stock->refresh());
    }

    public function destroy(Stock $stock): JsonResponse
    {
        $stock->delete();
        return new JsonResponse(status: 204);
    }
}
