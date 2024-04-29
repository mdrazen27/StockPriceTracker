<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlphaVantageApi
{
    const BASE_URL = 'https://www.alphavantage.co/query';

    public function getStockPrices(int $stockId, string $symbol, int $interval = 1, string $outputsize = 'compact', ?string $month = null): array|null
    {
        if(!$month){
            $month = date('Y-m');
        }
        try{
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
        } catch (\Error|\Exception $e){
            Log::error($e->getMessage(), ['Trying to fetch stock prices from Alpha Vantage API']);
            return null;
        }

        $status = $response->status();
        if ($status === 200) {
            $dataObject = $response->object();
            $metaData = $dataObject->{"Meta Data"} ?? null;
            if ($metaData) {
                $data = $dataObject->{"Time Series ($interval)"} ?? null;
                $parsedValues = [];
                foreach ($data as $dataKey => $dataValues) {
                    $currentRow = ['time' => $dataKey, 'stock_id' => $stockId];
                    foreach ($dataValues as $valueKey => $value) {
                        // response returns open value as '1. open'...
                        $currentRow[explode(' ', $valueKey)[1]] = $value;
                    }
                    $parsedValues[] = $currentRow;
                }
                return $parsedValues;
            }
        }
        $this->logError($response);
        return null;
    }

    private function logError($response): void
    {
        Log::error(json_encode(['body' => $response->body(), 'request_url' => $response->effectiveUri()]));
    }
}
