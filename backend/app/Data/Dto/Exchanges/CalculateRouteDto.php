<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges;


class CalculateRouteDto
{
    public function __construct(
        protected array $route,
        protected string $estimatedPrice,
        protected string $realPrice,
        protected string $rate,
        protected string $fee,
        protected int $precision,
    ) {}

    public function getRoute(): array
    {
        return $this->route;
    }

    public function getEstimatedPrice(): string
    {
        return $this->estimatedPrice;
    }

    public function getRealPrice(): string
    {
        return $this->realPrice;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function getFee(): string
    {
        return $this->rate;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
}
