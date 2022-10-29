<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Clients;

use App\Data\Dto\Exchanges\CurrencyDto;
use ccxt\Exchange;
use Illuminate\Support\Collection;

abstract class AbstractClient implements ClientInterface
{
    protected const FIAT_MONEY_NETWORK = 'FIAT_MONEY';
    protected const DEFAULT_PRECISION = 18;

    protected ?Collection $currencies = null;
    protected Exchange $client;

    public function getCurrencies(bool $refresh = false): Collection
    {
        if ($this->currencies !== null && !$refresh) {
            return $this->currencies;
        }

        $collection = collect();

        foreach ($this->client->fetch_currencies() as $key => $value) {
            if (!isset($value['networks'][0]['network'])) {
                continue;
            }

            $collection->push(new CurrencyDto(
                mb_strtoupper($value['name']),
                mb_strtoupper($value['code']),
                $value['precision'] ?: static::DEFAULT_PRECISION,
                $value['networks'][0]['network'] !== static::FIAT_MONEY_NETWORK
            ));
        }

        return $collection;
    }
}
