<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges\Interfaces;

interface TickerDtoInterface
{
    /**
     * Get base currency name
     *
     * @return string
     */
    public function getBaseSymbol(): string;

    /**
     * Get quote currency name
     *
     * @return string
     */
    public function getQuoteSymbol(): string;

    /**
     * Get ask price
     *
     * @return float
     */
    public function getAskPrice(): float;

    /**
     * Get bid price
     *
     * @return float
     */
    public function getBidPrice(): float;

    /**
     * Get base volume
     *
     * @return float
     */
    public function getBaseVolume(): float;

    /**
     * Get quote volume
     *
     * @return float
     */
    public function getQuoteVolume(): float;
}
