<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class Stock extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];

    public function stockPrices(): HasMany
    {
        return $this->hasMany(StockPrice::class);
    }

    public function latestPrice(): HasOne
    {
        // latestOfMany cant be used because of the composite primary key
        return $this->hasOne(StockPrice::class)->latest('time')->limit(1);
    }

    public function delete(): void
    {
        $this->stockPrices()->delete();
        parent::delete();
    }

    public static function cacheStockLatestPrices(Stock $stock, array $prices): void
    {
        $dataToCache = $stock->toArray();
        // adapted structure so it matches results when they are fetched from mysql
        $dataToCache['latest_price'] = $prices;
        $dataToCache['stock_id'] = $stock->id;
        Cache::set(strtoupper($stock['name']), $dataToCache, 60);
    }

    public static function calculatePriceDifference($stocks, string $dateFrom, string $dateTo): array
    {
        $stocksAsArray = [];
        foreach ($stocks as $stock) {
            if (isset($stocksAsArray[$stock->name])) {
                $stocksAsArray[$stock->name]['start_price'] = $stock->high;
                try {
                    // in case of bankruptcy stock value can drop to 0
                    $difference = ($stocksAsArray[$stock->name]['end_price'] - $stock->high) / $stock->high;
                } catch (\DivisionByZeroError) {
                    $difference = 0;
                } catch (\TypeError) {
                    $difference = 'Unknown';
                }
                $stocksAsArray[$stock->name]['difference'] = number_format($difference, 7);
            } else {
                $stocksAsArray[$stock->name] = [
                    'name' => $stock->name,
                    'symbol' => $stock->symbol,
                    'description' => $stock->description,
                    'end_price' => $stock->time === $dateTo ? $stock->high : 'Unknown',
                    'start_price' => $stock->time === $dateFrom ? $stock->high : 'Unknown',
                    'difference' => 'Unknown'
                ];
            }
        }
        return array_values($stocksAsArray);
    }

    public static function getOrCacheLatestPrice($name)
    {
        $nameUpper = strtoupper($name);
        $stock = Cache::get($nameUpper);
        if (!$stock) {
            $stock = Stock::with('latestPrice')
                ->where('name', $nameUpper)
                ->first();
            if ($stock) {
                Cache::set($nameUpper, $stock->toArray(), 60);
            }
        }
        return $stock;
    }

    public static function getStocksLatestPrices($names): array
    {
        $stocks = self::with('latestPrice');
        if ($names) {
            $data = [];
            $noResultsFound = [];
            $stockNames = explode(',', $names);
            foreach ($stockNames as $stockName) {
                $dataFromCache = Cache::get(strtoupper($stockName));
                if ($dataFromCache) {
                    $data[] = $dataFromCache;
                } else {
                    $noResultsFound[] = $stockName;
                }
            }
            //instead of caching one by one retrieves rest in one query to save time on database connection
            $stocks->whereIn('name', $noResultsFound);
            array_push($data, ...$stocks->get());
            return $data;
        }
        return $stocks->get();
    }
}
