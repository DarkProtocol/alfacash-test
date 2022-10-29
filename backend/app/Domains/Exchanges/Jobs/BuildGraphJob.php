<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Jobs;

use App\Common\Support\Str;
use App\Data\Dto\Exchanges\Interfaces\CurrencyDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\MarketDtoInterface;
use App\Domains\Exchanges\Clients\ClientInterface;
use Laudis\Neo4j\Contracts\UnmanagedTransactionInterface;
use Lucid\Units\Job;

class BuildGraphJob extends Job
{
    public function __construct(
        protected ClientInterface $exchangeClient,
        protected UnmanagedTransactionInterface $neoTx,
    ) {
    }

    public function handle()
    {
        // remove all exchange relations
        $this->neoTx->run(sprintf('MATCH ()-[r: %s]->() DELETE r', $this->exchangeClient::getSlug()));

        foreach ($this->exchangeClient->getMarket(false) as $market) {
            /** @var MarketDtoInterface $market */
            $this->createCurrencyIfNotExists($market->getBaseCurrency());
            $this->createCurrencyIfNotExists($market->getQuoteCurrency());

            $askRate = Str::bcMathPrepare($market->getTicker()->getAskPrice(), 18);

            $bidRate = bcdiv(
                '1',
                Str::bcMathPrepare($market->getTicker()->getBidPrice(), 18),
                18
            );

            $this->neoTx->run(sprintf('
                MATCH (x: Currency {ticker: $baseTicker}), (y: Currency {ticker: $quoteTicker})

                CREATE (x)-[:%s {
                    price: $basePrice,
                    thVolume: $baseVolume,
                    from: $baseTicker,
                    to: $quoteTicker,
                    fee: $fee,
                    baseMinOrder: $baseMinOrder,
                    baseMaxOrder: $baseMinOrder,
                    minOrder: $baseMinOrder,
                    maxOrder: $baseMaxOrder,
                    orderPrecision: $orderPrecision
                }]->(y)

                CREATE (y)-[:%s {
                    price: $quotePrice,
                    thVolume: $quoteVolume,
                    from: $baseTicker,
                    to: $quoteTicker,
                    fee: $fee,
                    baseMinOrder: $baseMinOrder,
                    baseMaxOrder: $baseMinOrder,
                    minOrder: $quoteMinOrder,
                    maxOrder: $quoteMaxOrder,
                    orderPrecision: $orderPrecision
                }]->(x)
                ',
                $this->exchangeClient::getSlug(),
                $this->exchangeClient::getSlug(),
            ), [
                // full info for from BASE to QUOTE currency relationship
                'baseTicker' => $market->getBaseCurrency()->getSymbol(),
                'basePrice' => (float) Str::cleanDecimals(bcsub(
                    $askRate,
                    bcmul($askRate, (string) $market->getTakerFee(), 18),
                    18
                )),
                'baseVolume' => $market->getTicker()->getBaseVolume() / 24 / 3,
                'baseMinOrder' => $market->getMinOrder(),
                'baseMaxOrder' => $market->getMaxOrder(),

                // full info for from QUOTE to BASE currency relationship
                'quoteTicker' => $market->getQuoteCurrency()->getSymbol(),
                'quotePrice' =>  (float) Str::cleanDecimals(bcsub(
                    $bidRate,
                    bcmul($bidRate, (string) $market->getTakerFee(), 18),
                    18
                )),
                'quoteVolume' => $market->getTicker()->getQuoteVolume() / 24 / 3,
                'quoteMinOrder' => (float) Str::cleanDecimals(bcdiv(
                    Str::bcMathPrepare($market->getMinOrder(), 18),
                    $bidRate,
                    18
                )),
                'quoteMaxOrder' => (float) Str::cleanDecimals(bcdiv(
                    Str::bcMathPrepare($market->getMaxOrder(), 18),
                    $bidRate,
                    18
                )),

                // common info for both relationships
                'fee' => $market->getTakerFee(),
                'orderPrecision' => $market->getPricePrecision(),
            ]);
        }
    }

    /**
     * Create currency if not exists
     *
     * @param CurrencyDtoInterface $currencyDto
     */
    protected function createCurrencyIfNotExists(CurrencyDtoInterface $currencyDto): void
    {
        $stmt = $this->neoTx->run(
            'MATCH (n: Currency {ticker: $ticker}) RETURN n',
            ['ticker' => $currencyDto->getSymbol()]
        );

        if (count($stmt->toArray()) === 0) {
            $this->neoTx->run(
                'CREATE (n: Currency {ticker: $ticker, precision: $precision})',
                [
                    'ticker' => $currencyDto->getSymbol(),
                    'precision' => $currencyDto->getPrecision(),
                ]
            );
        }
    }
}
