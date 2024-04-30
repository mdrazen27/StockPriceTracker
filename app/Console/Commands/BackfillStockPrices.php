<?php

namespace App\Console\Commands;

use App\Helpers\AlphaVantageApi;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill-stock-prices {date} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for backfilling stock prices for specific month, required parameters are date and
                              stock name';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = $this->argument('date');
        $name = $this->argument('name');
        $this->info("Backfilling $name stock prices for $date");
        if (!Carbon::canBeCreatedFromFormat($date, 'Y-m')) {
            $this->error('Invalid date format. Date must be of format Y-m');
            exit();
        }
        $stock = Stock::where('name', $name)->first();
        if (!$stock) {
            $this->error('Stock name not found, check spelling');
            exit();
        }

        $alphaVantageApi = new AlphaVantageApi();
        $alphaVantageApi->syncStockPrices($stock, $stock->symbol, outputsize: 'full', month: $date, cacheResult: false);
        echo "Done\n";
    }
}
