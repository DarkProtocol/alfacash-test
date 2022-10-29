<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Clients\Binance;

use App\Data\Dto\Exchanges\Interfaces\CurrencyDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\OrderBookDtoInterface;
use App\Data\Dto\Exchanges\Interfaces\TickerDtoInterface;
use App\Data\Dto\Exchanges\MarketDto;
use App\Data\Dto\Exchanges\OrderBookDto;
use App\Data\Dto\Exchanges\TickerDto;
use App\Domains\Exchanges\Clients\AbstractClient;
use App\Domains\Exchanges\Clients\ClientInterface;
use ccxt\binance;
use ccxt\ExchangeError;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Client extends AbstractClient implements ClientInterface
{
    protected const FIAT_MONEY_NETWORK = 'FIAT_MONEY';

    /**
     * @throws ExchangeError
     */
    public function __construct()
    {
        $this->client = new binance([
            'apiKey' => Config::get(sprintf('exchanges.%s.apiKey', static::getSlug())),
            'secret' => Config::get(sprintf('exchanges.%s.secret', static::getSlug())),
        ]);
    }

    public static function getSlug(): string
    {
        return 'BINANCE';
    }

    public function getTickers(): Collection
    {
        $tickers = collect();

        foreach ($this->client->fetch_tickers() as $key => $value) {
            $pair = explode('/', $key);

            if (count($pair) < 2) {
                continue;
            }

            $tickers->push(new TickerDto(
                mb_strtoupper($pair[0]),
                mb_strtoupper($pair[1]),
                (float) $value['info']['askPrice'],
                (float) $value['info']['bidPrice'],
                $value['baseVolume'],
                $value['quoteVolume'],
            ));
        }

        return $tickers;
    }

    public function getMarket(bool $withFiat = true): Collection
    {
        $currencies = $this->getCurrencies(true);
        $tickers = $this->getTickers();

        $markets = collect();

        foreach ($this->client->load_markets() as $key => $value) {
            if (!$value['active']) {
                continue;
            }

            /** @var CurrencyDtoInterface $baseCurrency */
            $baseCurrency = $currencies->first(function($item) use($value) {
                /** @var CurrencyDtoInterface $item */
                return $item->getSymbol() === mb_strtoupper($value['base']);
            });

            /** @var CurrencyDtoInterface $quoteCurrency */
            $quoteCurrency = $currencies->first(function($item) use($value) {
                /** @var CurrencyDtoInterface $item */
                return $item->getSymbol() === mb_strtoupper($value['quote']);
            });

            if (!$baseCurrency || !$quoteCurrency) {
                continue;
            }

            if (!$withFiat && (!$baseCurrency->getIsCrypto() || !$quoteCurrency->getIsCrypto())) {
                continue;
            }

            /** @var TickerDtoInterface $ticker */
            $ticker = $tickers->first(function($item) use($value) {
                /** @var TickerDtoInterface $item */
                return $item->getBaseSymbol() === mb_strtoupper($value['base']) &&
                        $item->getQuoteSymbol() === mb_strtoupper($value['quote']);
            });

            if (!$ticker) {
                continue;
            }

            $baseCurrency->setPrecision($value['precision']['base']);
            $quoteCurrency->setPrecision($value['precision']['base']);

            $markets->push(new MarketDto(
                $baseCurrency,
                $quoteCurrency,
                $ticker,
                $value['precision']['price'],
                $value['limits']['amount']['min'],
                $value['limits']['amount']['max'],
                $value['taker'],
            ));
        }

        return $markets;
    }

    public function getOrderBook(string $base, string $quote, int $limit): OrderBookDtoInterface
    {
        $orderBook = $this->client->fetch_order_book(sprintf('%s/%s', $base, $quote), $limit);
        return new OrderBookDto($orderBook['asks'] ?? [], $orderBook['bids'] ?? []);
    }
}
