<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges;

use App\Data\Dto\Exchanges\Interfaces\OrderBookDtoInterface;

class OrderBookDto implements OrderBookDtoInterface
{
    public function __construct(
        protected array $asks,
        protected array $bids,
    ) {}

    public function getAsks(): array
    {
        return $this->asks;
    }

    public function getBids(): array
    {
        return $this->bids;
    }
}
