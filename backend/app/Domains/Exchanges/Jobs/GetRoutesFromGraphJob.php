<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Jobs;

use App\Common\Support\NeoClient;

use App\Common\Support\Str;
use App\Domains\Exchanges\Clients\ClientInterface as ExClient;
use Laudis\Neo4j\Databags\SummarizedResult;
use Lucid\Units\Job;

class GetRoutesFromGraphJob extends Job
{
    public function __construct(
        protected string $baseCurrency,
        protected string $quoteCurrency,
        protected string $amount,
        protected ExClient $exClient,
    ) {
    }

    public function handle(NeoClient $neoClient): SummarizedResult
    {
        return $neoClient->run(sprintf('
            MATCH p = (start :Currency {ticker: $base})-[:%s*0..4]->(end :Currency {ticker: $quote})
            UNWIND NODES(p) AS n
            WITH p, start, end, SIZE(COLLECT(DISTINCT n)) AS len
            WHERE len = LENGTH(p) + 1
            WITH
                p,
                reduce(res = $amount, r IN relationships(p) | CASE
                    WHEN res * 1.2 > r.thVolume THEN res * -1
                    WHEN res * 1.2 < r.minOrder AND res > 0 then res * -1
                    WHEN res > r.maxOrder then res * -1
                    ELSE res * r.price
                    END
                ) as cost
            WHERE cost > 0
            RETURN p, cost
            ORDER BY  cost DESC
            LIMIT 10
        ', $this->exClient::getSlug()), [
            'base' => $this->baseCurrency,
            'quote' => $this->quoteCurrency,
            'amount' => (float) Str::cleanDecimals($this->amount),
        ]);
    }
}
