<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\RateCurrencyNotExistException;

class ExchangerRateCurrency
{
    protected array $rates = [];
    protected const DELIMITER = '/';

    public function __construct()
    {
        $this->setRate(Currency::EUR, Currency::RUB, 80);
        $this->setRate(Currency::USD, Currency::RUB, 70);
        $this->setRate(Currency::EUR, Currency::USD, 1);
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @param float $rate
     * @return void
     */
    public function setRate(Currency $currencyFrom, Currency $currencyTo, float $rate): void
    {
        $rateKey = $this->createRelationKey($currencyFrom, $currencyTo);
        $this->rates[$rateKey] = $rate;

        $secondRateKey = $this->createRelationKey($currencyTo, $currencyFrom);
        $secondRate = (float)bcdiv('1', (string)$rate, 10);
        $this->rates[$secondRateKey] = round($secondRate, 6);
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @return string
     */
    protected function createRelationKey(Currency $currencyFrom, Currency $currencyTo): string
    {
        return $currencyFrom->name . self::DELIMITER . $currencyTo->name;
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @return float
     * @throws RateCurrencyNotExistException
     */
    public function getRate(Currency $currencyFrom, Currency $currencyTo): float
    {
        $rateKey = $this->createRelationKey($currencyFrom, $currencyTo);
        $rate = $this->rates[$rateKey] ?? null;
        if ($rate !== null) {
            return $rate;
        }

        throw new RateCurrencyNotExistException();
    }

    public function convert(Currency $currencyFrom, Currency $currencyTo, float $amount): Funds
    {
        $rate = $this->getRate($currencyFrom, $currencyTo);
        $resultConvert = bcmul((string)$amount, (string)$rate, 10);
        $resultConvert = round((float)$resultConvert, 2);
        return new Funds($currencyTo, $resultConvert);
    }
}