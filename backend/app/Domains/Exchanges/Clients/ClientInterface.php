<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Clients;

use App\Data\Dto\Exchanges\Interfaces\CurrencyDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\MarketDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\OrderBookDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\TickerDtoInterface;
use Illuminate\Support\Collection;

interface ClientInterface
{
    /**
     * Get client slug
     *
     * @return string
     */
    public static function getSlug(): string;

    /**
     * Get currencies
     *
     * @param bool $refresh
     * @return Collection<CurrencyDtoInterface>
     */
    public function getCurrencies(bool $refresh = false): Collection;

    /**
     * Get market data
     *
     * @param bool $withFiat
     * @return Collection<MarketDtoInterface>
     */
    public function getMarket(bool $withFiat = true): Collection;

    /**
     * Get tickers
     *
     * @return Collection<TickerDtoInterface>
     */
    public function getTickers(): Collection;

    /**
     * Get order book
     *
     * @param string $base
     * @param string $quote
     * @param int $limit
     * @return OrderBookDtoInterface
     */
    public function getOrderBook(string $base, string $quote, int $limit): OrderBookDtoInterface;
}
