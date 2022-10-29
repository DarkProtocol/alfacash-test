<?php

declare(strict_types=1);

namespace App\Data\Dto\Exchanges\Interfaces;

interface CurrencyDtoInterface
{
    /**
     * Get currency name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getSymbol(): string;

    /**
     * Get currency precision
     *
     * @return int
     */
    public function getPrecision(): int;

    /**
     * Set currency precision
     */
    public function setPrecision(int $precision): void;

    /**
     * Get currency is crypto
     *
     * @return bool
     */
    public function getIsCrypto(): bool;
}
