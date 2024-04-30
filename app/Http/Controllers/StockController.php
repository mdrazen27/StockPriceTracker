<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function getStocksLatestPrice(Request $request): JsonResponse
    {
        return new JsonResponse(Stock::getStocksLatestPrices($request->stocks));
    }

    public function getStockLatestPrice(string $name): JsonResponse
    {
        return new JsonResponse(Stock::getOrCacheLatestPrice($name));
    }

    public function getStockPriceDifference(Request $request): JsonResponse
    {
        $request->validate([
            'dateFrom' => 'required|date|date_format:Y-m-d H:i:00',
            'dateTo' => 'required|date|date_format:Y-m-d H:i:00',
            'stocks' => 'required|string',
        ]);
        $stocks = Stock::whereIn('stocks.name', explode(',', $request->stocks))
            ->join('stock_prices', 'stocks.id', '=', 'stock_prices.stock_id')
            ->where(function ($query) use ($request) {
                $query->where('stock_prices.time', '=', $request->dateFrom)
                    ->orWhere('stock_prices.time', '=', $request->dateTo);
            })
            ->orderByDesc('stock_prices.time')
            ->get();
        if (!$stocks->count()) {
            return new JsonResponse(['message' => 'No data for selected date times']);
        }
        $priceDifferences = Stock::calculatePriceDifference($stocks, $request->dateFrom, $request->dateTo);

        return new JsonResponse($priceDifferences);
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
