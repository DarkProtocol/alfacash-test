<?php

declare(strict_types=1);

namespace App\Services\Exchange\Features;

use App\Common\Http\Exceptions\ApiException;
use App\Common\Http\Exceptions\InternalError;
use App\Common\Support\Str;
use App\Data\Dto\Exchanges\CalculateRouteDto;
use App\Domains\Exchanges\Jobs\CalculateRouteJob;
use App\Domains\Exchanges\Jobs\GetRoutesFromGraphJob;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Laudis\Neo4j\Databags\SummarizedResult;
use Lucid\Units\Feature;
use App\Domains\Exchanges\Requests\BestRateRequest;
use App\Domains\Exchanges\Clients\Binance\Client as BinanceClient;
use Throwable;
use Exception;

class BestRateFeature extends Feature
{
    /**
     * @throws ApiException
     * @throws InternalError
     */
    public function handle(BestRateRequest $request, Logger $logger): Collection
    {
        try {
            $exClient = resolve(BinanceClient::getSlug());
        } catch (Throwable $e) {
            $logger->error($e, ['sentry' => 'baseFeatureTag']);
            throw new InternalError();
        }

        $pair = explode('_', $request->route('pair'));
        $amount = Str::cleanDecimals($request->route('amount'));

        /** @var SummarizedResult $rows */
        $rows = $this->run(GetRoutesFromGraphJob::class, [
            'baseCurrency' => $pair[0],
            'quoteCurrency' => $pair[1],
            'amount' => $amount,
            'exClient' => $exClient,
        ]);

        if ($rows->count() === 0) {
            throw new ApiException([], 'No routes found');
        }

        $collection = collect();

        foreach ($rows as $row) {
            try {
                /** @var CalculateRouteDto $dto */
                $dto = $this->run(CalculateRouteJob::class, [
                    'exClient' => $exClient,
                    'row' => $row,
                    'amount' => $amount,
                ]);

                $collection->push([
                    'path' => implode(' -> ', $dto->getRoute()),
                    'estimatedGraphPrice' => Str::bcround($dto->getEstimatedPrice(), $dto->getPrecision()),
                    'priceWithoutFee' => Str::bcround($dto->getRealPrice(), $dto->getPrecision()),
                    'realPrice' => bcsub($dto->getRealPrice(), $dto->getFee(), $dto->getPrecision()),
                    'rateWithoutFee' => Str::bcround($dto->getRate(), $dto->getPrecision()),
                    'fee' => Str::bcround($dto->getFee(), $dto->getPrecision()),
                ]);
            } catch (Exception $e) {
                $logger->error($e->getMessage(), [
                    'sentryTag' => 'bestRate',
                    'pair' => $request->route('pair'),
                ]);
            }
        }

        if ($collection->count() === 0) {
            throw new ApiException([], 'Undefined routes');
        }

        return $collection->sort(function ($a, $b) {
            return bccomp($b['realPrice'], $a['realPrice'], 18);
        })->values();
    }
}
