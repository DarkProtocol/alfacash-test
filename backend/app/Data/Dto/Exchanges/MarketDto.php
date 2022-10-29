<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges;

use App\Data\Dto\Exchanges\Interfaces\CurrencyDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\MarketDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\TickerDtoInterface;

class MarketDto implements MarketDtoInterface
{
    public function __construct(
        protected CurrencyDtoInterface $baseCurrency,
        protected CurrencyDtoInterface $quoteCurrency,
        protected TickerDtoInterface $ticker,
        protected int $pricePrecision,
        protected float $minOrder,
        protected float $maxOrder,
        protected float $takerFee,
    ) {}


    public function getBaseCurrency(): CurrencyDtoInterface
    {
        return $this->baseCurrency;
    }

    public function getQuoteCurrency(): CurrencyDtoInterface
    {
        return $this->quoteCurrency;
    }

    public function getTicker(): TickerDtoInterface
    {
        return $this->ticker;
    }

    public function getPricePrecision(): int
    {
        return $this->pricePrecision;
    }

    public function getMinOrder(): float
    {
        return $this->minOrder
            ;
    }

    public function getMaxOrder(): float
    {
        return $this->maxOrder;
    }

    public function getTakerFee(): float
    {
        return $this->takerFee;
    }
}
