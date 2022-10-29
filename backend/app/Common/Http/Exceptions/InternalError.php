<?php

declare(strict_types=1);

namespace App\Common\Http\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InternalError extends ApiException
{
    #[Pure]
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(null, 'Internal error', 500, $previous);
    }
}
