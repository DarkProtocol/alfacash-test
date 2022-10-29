<?php

declare(strict_types=1);

namespace App\Services\Exchange\Providers;

use App\Domains\Exchanges\Clients\Binance\Client as BinanceClient;
use App\Services\Exchange\Console\UpdateExchangeGraphCommand;
use Illuminate\Support\ServiceProvider;

class ExchangeServiceProvider extends ServiceProvider
{
    /** @var string[] */
    protected static array $commands = [
        UpdateExchangeGraphCommand::class,
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom([
            realpath(__DIR__ . '/../database/migrations')
        ]);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands(static::$commands);
        $this->app->bind(BinanceClient::getSlug(), BinanceClient::class);

        $this->registerResources();
    }

    protected function registerResources(): void
    {
    }
}
