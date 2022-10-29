<?php

declare(strict_types=1);

namespace App\Foundation;

use App\Common\Support\NeoClient;
use App\Services\Exchange\Providers\ExchangeServiceProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->register(ExchangeServiceProvider::class);
        $this->app->singleton(NeoClient::class);
    }
}
