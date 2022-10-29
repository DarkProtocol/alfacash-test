<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges\Interfaces;

interface MarketDtoInterface
{
    /**
     * Get base currency name
     *
     * @return CurrencyDtoInterface
     */
    public function getBaseCurrency(): CurrencyDtoInterface;

    /**
     * Get quote currency name
     *
     * @return CurrencyDtoInterface
     */
    public function getQuoteCurrency(): CurrencyDtoInterface;

    /**
     * Get ticker
     *
     * @return TickerDtoInterface
     */
    public function getTicker(): TickerDtoInterface;

    /**
     * Get price precision
     *
     * @return int
     */
    public function getPricePrecision(): int;

    /**
     * Get min order
     *
     * @return float
     */
    public function getMinOrder(): float;

    /**
     * Get max order
     *
     * @return float
     */
    public function getMaxOrder(): float;

    /**
     * Get taker fee
     *
     * @return float
     */
    public function getTakerFee(): float;
}
