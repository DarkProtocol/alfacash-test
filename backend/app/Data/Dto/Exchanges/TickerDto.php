<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges;

use App\Data\Dto\Exchanges\Interfaces\TickerDtoInterface;

class TickerDto implements TickerDtoInterface
{
    public function __construct(
        protected string $baseSymbol,
        protected string $quoteSymbol,
        protected float $askPrice,
        protected float $bidPrice,
        protected float $baseVolume,
        protected float $quoteVolume,
    ) {}

    public function getBaseSymbol(): string
    {
        return $this->baseSymbol;
    }

    public function getQuoteSymbol(): string
    {
        return $this->quoteSymbol;
    }

    public function getAskPrice(): float
    {
        return $this->askPrice;
    }

    public function getBidPrice(): float
    {
        return $this->bidPrice;
    }

    public function getBaseVolume(): float
    {
        return $this->baseVolume;
    }

    public function getQuoteVolume(): float
    {
        return $this->quoteVolume;
    }
}
