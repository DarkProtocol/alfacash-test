<?php

use App\Domains\Exchanges\Clients\Binance\Client as BinanceClient;

return [
    BinanceClient::getSlug() => [
        'apiKey' => env('BINANCE_API_KEY'),
        'secret' => env('BINANCE_API_SECRET'),
    ],
];
