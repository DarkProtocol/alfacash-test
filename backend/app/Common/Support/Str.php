<?php

declare(strict_types=1);

namespace App\Common\Support;

use Illuminate\Support\Str as BaseStr;

class Str extends BaseStr
{
    public static function cleanDecimals(string $value): string
    {
        $pos = strpos($value, '.');

        if ($pos === false) {
            return $value;
        }

        return rtrim(rtrim($value, '0'), '.');
    }

    public static function bcMathPrepare(float $value, int $precision): string
    {
        return number_format($value, $precision, '.', '');
    }

    public static function bcround(string $number, int $precision = 0): string
    {
        if (str_contains($number, '.')) {
            if ($number[0] != '-') {
                return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return $number;
    }
}
