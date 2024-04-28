<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
