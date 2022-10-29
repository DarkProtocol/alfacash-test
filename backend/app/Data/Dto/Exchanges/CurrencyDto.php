<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges;

use App\Data\Dto\Exchanges\Interfaces\CurrencyDtoInterface;

class CurrencyDto implements CurrencyDtoInterface
{
    public function __construct(
        protected string $name,
        protected string $symbol,
        protected int $precision,
        protected bool $isCrypto
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    public function getIsCrypto(): bool
    {
        return $this->isCrypto;
    }
}
