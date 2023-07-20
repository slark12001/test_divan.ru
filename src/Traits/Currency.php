<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;

/**
 * Designed to get rid of code duplication and added basic properties for the implementation
 * of work with the main currency and the list of currencies
 */
trait Currency
{
    /**
     * A list of currencies where the key is the name of the currency and the value is the activity of that currency
     * Example: USD => true
     * @var bool[]
     */
    protected array $currencies = [];
    /**
     * Main currency
     * @var \App\Enums\Currency
     */
    protected \App\Enums\Currency $mainCurrency;

    /**
     * Adds a currency if there is none, otherwise it will throw an exception
     * @param \App\Enums\Currency $currency

     * @throws CurrencyExistException
     * @throws CurrencyNotExistException
     */
    public function addCurrency(\App\Enums\Currency $currency): void
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
     * Checking that this currency is present and it is active
     * @param \App\Enums\Currency $currency
     * @return bool
     */
    public function checkCurrencyExist(\App\Enums\Currency $currency): bool
    {
        $currencyExist = $this->currencies[$currency->name] ?? null;
        return $currencyExist === true;
    }

    /**
     * Making sure that the main currency is set
     * @return bool
     */
    protected function mainCurrencyIsSet(): bool
    {
        return isset($this->mainCurrency);
    }

    /**
     * Sets the main currency, if one is added, otherwise it will throw an exception
     * @param \App\Enums\Currency $currency
     * @return $this
     * @throws CurrencyNotExistException
     */
    public function setMainCurrency(\App\Enums\Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }

        $this->mainCurrency = $currency;
        return $this;
    }

    /**
     * The main currency is returned if it is set, otherwise it will throw an exception
     * @return \App\Enums\Currency
     * @throws MainCurrencyIsNotSetException
     */
    public function getMainCurrency(): \App\Enums\Currency
    {
        if ($this->mainCurrencyIsSet() === false) {
            throw new MainCurrencyIsNotSetException();
        }

        return $this->mainCurrency;
    }

    /**
     * Returns a list of available currencies
     * @return array
     */
    public function listAvailableCurrencies(): array
    {
        $availableCurrencies = array_filter($this->currencies);
        return array_keys($availableCurrencies);
    }
}