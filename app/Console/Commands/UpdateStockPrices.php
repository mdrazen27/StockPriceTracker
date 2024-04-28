<?php

namespace App\Console\Commands;

use App\Models\AlphaVantageApi;
use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-stock-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for updating stock prices based on response from Alpha Vantage API.
                              Command is scheduled to run in regular 1 minute intervals';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $stocks = Stock::all();
        $alphaVantageApi = new AlphaVantageApi();
        $pricesFromApi = [];
        foreach ($stocks as $stock) {
            $apiResponse = $alphaVantageApi->getStockPrices($stock->id, $stock->symbol);
            if ($apiResponse) {
                $this->cacheLatestPrices($stock, $apiResponse[0]);
                array_push($pricesFromApi, ...$apiResponse);
            }
        }
        if ($pricesFromApi) {
            foreach (array_chunk($pricesFromApi, 500) as $chunk) {
                StockPrice::insertOrIgnore($chunk);
            }
        }
    }

    private function cacheLatestPrices($stock, $prices): void
    {
        $dataToCache = $stock->toArray();
        // adapted structure so it matches results when they are fetched from mysql
        $dataToCache['latest_price'] = $prices;
        $dataToCache['stock_id'] = $stock->id;
        Cache::set($stock['name'], $dataToCache, 60);
    }
}
