<?php

declare(strict_types=1);

namespace App\Services\Exchange\Console;

use App\Common\Console\UnitDispatcher;
use App\Common\Support\NeoClient;
use App\Domains\Exchanges\Clients\ClientInterface;
use App\Domains\Exchanges\Jobs\BuildGraphJob;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Throwable;

class UpdateExchangeGraphCommand extends Command
{
    protected $description = 'Update exchange graph';
    protected $signature = 'exchanges:update-graph {exchange : Exchange slug}';


    public function handle(
        UnitDispatcher $dispatcher,
        NeoClient $neoClient,
        Logger $logger,
    ): void
    {
        if (!$this->argument('exchange')) {
            $this->error('Invalid operation');
            return;
        }

        try {
            $neoClient->transaction(function (UnmanagedTransactionInterface $tx) use ($dispatcher) {
                $dispatcher->run(BuildGraphJob::class, [
                    'exchangeClient' => resolve(mb_strtoupper($this->argument('exchange'))),
                    'neoTx' => $tx,
                ]);
            });
            $this->info('Graph successfully built!');
        } catch (Throwable $e) {
            $logger->error($e->getMessage(), ['tag' => 'sentryTag']);
            $this->error($e->getMessage());
            throw $e;
        }
    }
}
