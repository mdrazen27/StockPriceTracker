<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Stock::insert(['id' => 1, 'name' => 'test', 'symbol' => 'test']);
        Stock::insert(['id' => 2, 'name' => 'test2', 'symbol' => 'test2']);
        Stock::insert(['id' => 3, 'name' => 'test3', 'symbol' => 'test3']);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_stock_price_difference_pass(): void
    {
        $query = http_build_query(['dateFrom' => '2024-01-01 12:00:00', 'dateTo' => '2024-12-31 12:00:00', 'stocks' => 'test']);
        $url = "api/stocks-price-difference?$query";

        StockPrice::insert(
            ['time' => '2024-01-01 12:00:00', 'open' => 1, 'high' => 500, 'low' => 1, 'close' => 1, 'volume' => 1, 'stock_id' => 1],
        );
        $response = $this->get($url, ['Accept' => 'application/json']);
        $json1 = $response->json(0);
        $response->assertStatus(200);
        $this->assertEquals(500, $json1['start_price']);
        $this->assertEquals('Unknown', $json1['end_price']);
        $this->assertEquals('Unknown', $json1['difference']);


        StockPrice::insert(
            ['time' => '2024-12-31 12:00:00', 'open' => 1, 'high' => 100, 'low' => 1, 'close' => 1, 'volume' => 1, 'stock_id' => 1],
        );

        $response2 = $this->get($url, ['Accept' => 'application/json']);
        $json2 = $response2->json(0);
        $response->assertStatus(200);
        $this->assertEquals(500, $json2['start_price']);
        $this->assertEquals(100, $json2['end_price']);
        $this->assertEquals((100 - 500) / 500, $json2['difference']);
    }

    public function test_get_stock_price_difference_multiple_pass(): void
    {
        $query = http_build_query(['dateFrom' => '2024-01-01 12:00:00', 'dateTo' => '2024-12-31 12:00:00', 'stocks' => 'test,test2']);
        $url = "api/stocks-price-difference?$query";
        StockPrice::insert(
            [
                ['time' => '2024-01-01 12:00:00', 'open' => 1, 'high' => 100, 'low' => 1, 'close' => 1, 'volume' => 1, 'stock_id' => 1],
                ['time' => '2024-12-31 12:00:00', 'open' => 1, 'high' => 100, 'low' => 1, 'close' => 1, 'volume' => 1, 'stock_id' => 2],
            ]
        );
        $response = $this->get($url, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $responseJson = $response->json();
        $json1 = $responseJson[0];
        $this->assertEquals('Unknown', $json1['start_price']);
        $this->assertEquals(100, $json1['end_price']);
        $this->assertEquals('Unknown', $json1['difference']);
        $json2 = $responseJson[1];
        $this->assertEquals(100, $json2['start_price']);
        $this->assertEquals('Unknown', $json2['end_price']);
        $this->assertEquals('Unknown', $json2['difference']);
    }

    public function test_get_stock_price_difference_multiple_validation_fail(): void
    {
        $query = http_build_query(['dateFrom' => '2024-01-01 12:00:12', 'dateTo' => '2024-22-31 12:00:00', 'stocks' => '']);
        $url = "api/stocks-price-difference?$query";
        $response = $this->get($url, ['Accept' => 'application/json']);
        $response->assertStatus(422);
        $data = $response->json();
        $this->assertEquals('The date from field must match the format Y-m-d H:i:00. (and 3 more errors)', $data['message']);
        $this->assertEquals('The date from field must match the format Y-m-d H:i:00.', $data['errors']['dateFrom'][0]);
        $this->assertEquals('The date to field must be a valid date.', $data['errors']['dateTo'][0]);
        $this->assertEquals('The date to field must match the format Y-m-d H:i:00.', $data['errors']['dateTo'][1]);
        $this->assertEquals('The stocks field is required.', $data['errors']['stocks'][0]);
    }

    public function test_get_stock_price_difference_multiple_no_results_pass(): void
    {
        $query = http_build_query(['dateFrom' => '2024-01-01 12:00:00', 'dateTo' => '2024-12-31 12:00:00', 'stocks' => 'test5']);
        $url = "api/stocks-price-difference?$query";
        $response = $this->get($url, ['Accept' => 'application/json']);
        $response->assertStatus(200);
        $this->assertEquals('No data for selected date times', $response->json('message'));
    }
}
