<?php

declare(strict_types=1);

namespace App\Common\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait AutoUUID
{
    /**
     * Generate UUID
     *
     * @return string
     */
    public static function generateUUID(): string
    {
        return Str::orderedUuid()->toString();
    }

    /**
     * Boot function from laravel.
     */
    protected static function bootAutoUUID(): void
    {
        static::creating(static function (Model $model) {
            if (!$model->{$model->getKeyName()}) {
                $model->{$model->getKeyName()} = self::generateUUID();
            }
        });
    }
}
