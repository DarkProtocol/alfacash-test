<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Jobs;

use App\Common\Http\Exceptions\ApiException;
use App\Common\Support\Str;
use App\Data\Dto\Exchanges\CalculateRouteDto;
use App\Domains\Exchanges\Clients\ClientInterface;
use JetBrains\PhpStorm\Pure;
use Laudis\Neo4j\Types\CypherMap;
use Lucid\Units\Job;
use Exception;

class CalculateRouteJob extends Job
{
    public function __construct(
        protected ClientInterface $exClient,
        protected CypherMap $row,
        protected string $amount
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(): CalculateRouteDto
    {
        $nodes = $this->row->get('p')->getNodes();
        $rels = $this->row->get('p')->getRelationships();

        $amount = $this->amount;
        $rate = '1';
        $resultFee = '0';
        $precision = 0;

        $route = [$nodes[0]->getProperty('ticker')];

        for ($i = 1; $i < count($nodes); $i++) {
            $prev = $nodes[$i - 1];
            $rel = $rels[$i - 1];

            $fee = (string) $rel->getProperty('fee');
            $minOrder = Str::bcMathPrepare($rel->getProperty('baseMinOrder'), 18);
            $orderBook = $this->exClient->getOrderBook($rel->getProperty('from'), $rel->getProperty('to'), 100);
            $precision = $rel->getProperty('orderPrecision');

            list($amount, $vwRate) = $prev->getProperty('ticker') === $rel->getProperty('from') ?
                $this->calculateAsks($minOrder, $precision, $orderBook->getAsks(), $amount) :
                $this->calculateBids($minOrder, $precision, $orderBook->getBids(), $amount);

            $resultFee = bcadd(
                bcmul($resultFee, $vwRate, 18),
                bcmul($amount, $fee, 18),
                18,
            );
            $rate = bcmul($rate, $vwRate, 18);
            $route[] = $nodes[$i]->getProperty('ticker');
            $precision = $nodes[$i]->getProperty('precision');
        }

        return new CalculateRouteDto(
            $route,
            Str::bcMathPrepare($this->row->get('cost'), 18),
            $amount,
            $rate,
            $resultFee,
            $precision
        );
    }

    /**
     * Calculate asks
     *
     * @param string $minOrder
     * @param int $precision
     * @param array $orders
     * @param string $amount
     * @return array|null
     * @throws Exception
     */
    #[Pure]
    protected function calculateAsks (string $minOrder, int $precision, array $orders, string $amount): ?array
    {
        $finalAmount = '0';
        $finalRate = '0';

        foreach ($orders as $order) {
            $orderPrice = Str::bcMathPrepare($order[0], 18);
            $orderAmount = Str::bcMathPrepare($order[1], 18);

            if (bccomp($minOrder, $amount, 18) >= 0) {
                if (bccomp($finalRate, '0', 18) === 0) {
                    throw new Exception('Too small order');
                }

                return [$finalAmount, bcdiv($finalRate, $finalAmount, 18)];
            }

            if (bccomp($amount, $orderAmount, 18) === 1) {
                $amount = bcsub($amount, $orderAmount, $precision);
                $take = bcmul($orderAmount, $orderPrice, 18);

                $finalAmount = bcadd($finalAmount, $take, 18);
                $finalRate = bcadd($finalRate, bcmul($take, $orderPrice, 18), 18);
                continue;
            }

            $take = bcmul($amount, $orderPrice, 18);

            $finalAmount = bcadd($finalAmount, $take, 18);
            $finalRate = bcadd($finalRate, bcmul($take, $orderPrice, 18), 18);
            $amount = '0';
        }

        throw new Exception('Need more orders');
    }

    /**
     * Calculate bids
     *
     * @param string $minOrder
     * @param int $precision
     * @param array $orders
     * @param string $amount
     * @return array|null
     * @throws Exception
     */
    #[Pure]
    protected function calculateBids (string $minOrder, int $precision, array $orders, string $amount): ?array
    {
        $finalAmount = '0';
        $finalRate = '0';

        foreach ($orders as $order) {
            $orderPrice = Str::bcMathPrepare($order[0], 18);
            $orderAmount = Str::bcMathPrepare($order[1], 18);

            $amount = bcdiv($amount, $orderPrice, 18);

            if (bccomp($minOrder, $amount, 18) >= 0) {
                if (bccomp($finalRate, '0', 18) === 0) {
                    throw new Exception('Too small order');
                }

                return [$finalAmount, bcdiv($finalAmount, $finalRate, 18)];
            }

            if (bccomp($amount, $orderAmount, 18) === 1) {
                $amount = bcsub($amount, $orderAmount, $precision);

                $finalAmount = bcadd($finalAmount, $orderAmount, 18);
                $finalRate = bcadd($finalRate, $orderPrice, 18);

                $amount = bcmul($amount, $orderPrice, 18);
                continue;
            }

            $finalAmount = bcadd($finalAmount, $amount, 18);
            $finalRate = bcadd($finalRate, bcmul($amount, $orderPrice, 18), 18);
            $amount = '0';
        }

        throw new Exception('Need more orders');
    }
}
