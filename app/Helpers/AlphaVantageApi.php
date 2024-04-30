<?php

namespace App\Helpers;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlphaVantageApi
{
    const BASE_URL = 'https://www.alphavantage.co/query';

    public function syncStockPrices(Stock $stock, string $symbol, string $interval = '1min', string $outputsize = 'compact', ?string $month = null, bool $cacheResult = true): void
    {
        if (!$month) {
            $month = date('Y-m');
        }
        try {
            $response = Http::get(self::BASE_URL,
                [
                    'function' => 'TIME_SERIES_INTRADAY',
                    'apikey' => env('ALPHA_VANTAGE_API_KEY'),
                    'interval' => $interval,
                    'symbol' => $symbol,
                    'outputsize' => $outputsize,
                    'month' => $month,
                ]
            );
        } catch (\Error|\Exception $e) {
            Log::error($e->getMessage(), ['Trying to fetch stock prices from Alpha Vantage API']);
            return;
        }

        $status = $response->status();
        if ($status === 200) {
            $dataObject = $response->object();
            $metaData = $dataObject->{"Meta Data"} ?? null;
            if ($metaData) {
                $data = $dataObject->{"Time Series ($interval)"} ?? null;
                $parsedValues = [];
                foreach ($data as $dataKey => $dataValues) {
                    $currentRow = ['time' => $dataKey, 'stock_id' => $stock->id];
                    foreach ($dataValues as $valueKey => $value) {
                        // response returns open value as '1. open'...
                        $currentRow[explode(' ', $valueKey)[1]] = $value;
                    }
                    $parsedValues[] = $currentRow;
                }
                if (!empty($parsedValues)) {
                    if ($cacheResult) {
                        Stock::cacheStockLatestPrices($stock, $parsedValues[0]);
                    }
                    foreach (array_chunk($parsedValues, 500) as $chunk) {
                        StockPrice::insertOrIgnore($chunk);
                    }
                }
                return;
            }
        }
        $this->logError($response);
    }

    private function logError($response): void
    {
        Log::error(json_encode(['body' => $response->body(), 'request_url' => $response->effectiveUri()]));
    }
}
