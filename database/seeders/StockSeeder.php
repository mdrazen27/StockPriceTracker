<?php

namespace Database\Seeders;

use App\Models\Stock;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stock::insert([
            ['name' => 'Meta', 'symbol' => 'META', 'description' => 'Meta is publicly traded company...'],
            ['name' => 'Apple', 'symbol' => 'AAPL', 'description' => 'Apple is publicly traded company...'],
            ['name' => 'Microsoft', 'symbol' => 'MSFT', 'description' => null],
            ['name' => 'Google', 'symbol' => 'GOOGL', 'description' => 'Google is publicly traded company, these are A shares...'],
            ['name' => 'Nvidia', 'symbol' => 'NVDA', 'description' => 'Nvidia is publicly traded company...'],
        ]);
    }
}
