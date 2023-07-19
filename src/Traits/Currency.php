<?php

namespace App\Traits;

use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;

trait Currency
{
    protected array $currencies = [];
    protected \App\Currency $mainCurrency;

    /**
     * @param \App\Currency $currency
     * @return void
     * @throws CurrencyExistException
     * @throws CurrencyNotExistException
     */
    public function addCurrency(\App\Currency $currency): void
    {
        if ($this->checkCurrencyExist($currency) === true) {
            throw new CurrencyExistException(
                'The ' . $currency->name . ' currency has already been added'
            );
        }

        $this->currencies[$currency->name] = true;

        if ($this->mainCurrencyIsSet() === false) {
            $this->setMainCurrency($currency);
        }
    }

    /**
     * @param \App\Currency $currency
     * @return void
     * @throws CannotDeleteMainCurrencyException
     * @throws CurrencyNotExistException
     */
    public function removeCurrency(\App\Currency $currency): void
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }
        if ($this->mainCurrency === $currency) {
            throw new CannotDeleteMainCurrencyException();
        }
        $this->currencies[$currency->name] = false;
    }

    /**
     * @param \App\Currency $currency
     * @return bool
     */
    public function checkCurrencyExist(\App\Currency $currency): bool
    {
        return isset($this->currencies[$currency->name]);
    }

    /**
     * @return bool
     */
    protected function mainCurrencyIsSet(): bool
    {
        return isset($this->mainCurrency);
    }

    /**
     * @param \App\Currency $currency
     * @return $this
     * @throws CurrencyNotExistException
     */
    public function setMainCurrency(\App\Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }

        $this->mainCurrency = $currency;
        return $this;
    }

    /**
     * @return \App\Currency
     * @throws MainCurrencyIsNotSetException
     */
    public function getMainCurrency(): \App\Currency
    {
        if ($this->mainCurrencyIsSet() === false) {
            throw new MainCurrencyIsNotSetException();
        }

        return $this->mainCurrency;
    }

    /**
     * @return array
     */
    public function listAvailableCurrencies(): array
    {
        $availableCurrencies = array_filter($this->currencies);
        return array_keys($availableCurrencies);
    }
}