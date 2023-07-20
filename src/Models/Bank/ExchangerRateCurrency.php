<?php

declare(strict_types=1);

namespace App\Models\Bank;

use App\Common\Funds;
use App\Enums\Currency;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\RateCurrencyNotExistException;

class ExchangerRateCurrency
{
    /**
     * @var array
     */
    protected array $rates = [];
    /**
     * @var string
     */
    protected const DELIMITER = '/';

    /**
     * @param Bank $bank
     */
    public function __construct(
        protected readonly Bank $bank
    )
    {
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @param float $rate
     * @return void
     * @throws CurrencyNotExistException
     */
    public function setRate(Currency $currencyFrom, Currency $currencyTo, float $rate): void
    {
        $this->checkBankCurrencies($currencyFrom, $currencyTo);

        $rateKey = $this->createRelationKey($currencyFrom, $currencyTo);
        $this->rates[$rateKey] = $rate;

        $secondRateKey = $this->createRelationKey($currencyTo, $currencyFrom);
        $secondRate = (float)bcdiv('1', (string)$rate, 10);
        $this->rates[$secondRateKey] = round($secondRate, 6);
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @return void
     * @throws CurrencyNotExistException
     */
    protected function checkBankCurrencies(Currency $currencyFrom, Currency $currencyTo): void
    {
        if ($this->bank->checkCurrencyExist($currencyFrom) === false
            || $this->bank->checkCurrencyExist($currencyTo) === false
        ) {
            throw new CurrencyNotExistException();
        }
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
     * @throws RateCurrencyNotExistException|CurrencyNotExistException
     */
    public function getRate(Currency $currencyFrom, Currency $currencyTo): float
    {
        $this->checkBankCurrencies($currencyFrom, $currencyTo);

        $rateKey = $this->createRelationKey($currencyFrom, $currencyTo);
        $rate = $this->rates[$rateKey] ?? null;
        if ($rate !== null) {
            return $rate;
        }

        throw new RateCurrencyNotExistException();
    }

    /**
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @param float $amount
     * @return Funds
     * @throws CurrencyNotExistException
     * @throws RateCurrencyNotExistException
     */
    public function convert(Currency $currencyFrom, Currency $currencyTo, float $amount): Funds
    {
        $rate = $this->getRate($currencyFrom, $currencyTo);
        $resultConvert = bcmul((string)$amount, (string)$rate, 10);
        $resultConvert = round((float)$resultConvert, 2);
        return new Funds($currencyTo, $resultConvert);
    }
}