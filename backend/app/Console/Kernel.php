<?php

namespace App\Console;

use App\Domains\Exchanges\Clients\Binance\Client as BinanceClient;
use App\Services\Exchange\Console\UpdateExchangeGraphCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command(UpdateExchangeGraphCommand::class, [
                BinanceClient::getSlug()
            ])
            ->everyFiveMinutes()
            ->onOneServer()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
