<?php

declare(strict_types=1);

namespace App\Services\Exchange\Http\Controllers;

use App\Services\Exchange\Features\BestRateFeature;
use Lucid\Units\Controller;

class BestRateController extends Controller
{
    public function __invoke(): mixed
    {
        return $this->serve(BestRateFeature::class);
    }
}
