<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges\Interfaces;

interface OrderBookDtoInterface
{
    /**
     * Get asks
     *
     * @return array
     */
    public function getAsks(): array;

    /**
     * Get bids
     *
     * @return array
     */
    public function getBids(): array;
}
