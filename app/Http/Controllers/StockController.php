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

    public function getStockPriceDifference(Request $request): JsonResponse
    {
        $request->validate([
            'dateFrom' => 'required|date|date_format:Y-m-d H:i:s',
            'dateTo' => 'required|date|date_format:Y-m-d H:i:s',
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
        $stocksAsArray = [];
        foreach ($stocks as $stock) {
            if (isset($stocksAsArray[$stock->name])) {
                $stocksAsArray[$stock->name]['start_price'] = $stock->high;
                try {
                    // in case of bankruptcy stock value can drop to 0
                    $difference = ($stocksAsArray[$stock->name]['end_price'] - $stock->high) / $stock->high;
                } catch (\DivisionByZeroError) {
                    $difference = 0;
                }
                $stocksAsArray[$stock->name]['difference'] = number_format($difference, 7);
            } else {
                $stocksAsArray[$stock->name] = [
                    'name' => $stock->name,
                    'symbol' => $stock->symbol,
                    'description' => $stock->description,
                    'end_price' => $stock->high,
                    'difference' => 'Unknown'
                ];
            }
        }
        return new JsonResponse(array_values($stocksAsArray));
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
