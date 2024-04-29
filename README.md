# Stock Price Tracker

### About Project

***
App for automating interaction with Alpha Vantage API in real time, used to aggregate and collect stock
price data

### Requirements

****

```
"php": "^8.2"
"MariaDB": : "10.4.32"
"Redis" : "7.2.4"
ALPHA_VANTAGE_API_KEY in .env
```

**API key can be retrieved from <a href="https://www.alphavantage.co/support/#api-key">Vantage alpha API key</a>*

### How to run the app

***

1. Copy .env.example file into .env
2. Run commands :

``` 
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan schedue:run or php artisan schedue:work
php artisan serve
```

*run will update prices just once while work will keep doing it every minute and will
hit API limit for free key

### Stock prices backfill can be done with

***

`php artisan backfil-stock-prices <Y-m> <StockName>`

### Test can be run with

***

`php artisan test`

### Postman collection

**** 
Collection for endpoint testing can be found in root project folder.

**Dates should be changed.
