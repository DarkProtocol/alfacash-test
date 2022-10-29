<?php

declare(strict_types=1);

namespace App\Common\Support;

use Illuminate\Support\Facades\Config;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Closure;
use Throwable;

class NeoClient
{
    protected ClientInterface $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver(
                Config::get('database.connections.neo4j.driver'),
                sprintf(
                    '%s://%s:%s@%s',
                    Config::get('database.connections.neo4j.driver'),
                    Config::get('database.connections.neo4j.host'),
                    Config::get('database.connections.neo4j.password'),
                    Config::get('database.connections.neo4j.username'),
                )
            )
            ->build();
    }

    /**
     * @throws Throwable
     */
    public function transaction(Closure $closure) {
        $tx = $this->client->beginTransaction();

        try {
            $closure($tx);
            $tx->commit();
        } catch (Throwable $e) {
            $tx->rollback();
            throw $e;
        }
    }


    public function run(string $stmt, array $params): mixed
    {
        return $this->client->run($stmt, $params);
    }
}
