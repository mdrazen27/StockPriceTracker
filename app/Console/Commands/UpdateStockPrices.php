<?php

namespace App\Console\Commands;

use App\Helpers\AlphaVantageApi;
use App\Models\Stock;
use Illuminate\Console\Command;

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
        foreach ($stocks as $stock) {
            $alphaVantageApi->syncStockPrices($stock, $stock->symbol);
        }
    }
}
