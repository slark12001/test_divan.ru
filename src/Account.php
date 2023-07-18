<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;
use App\Exceptions\NotEnoughFundsException;

class Account
{
    /**
     * @var Funds[]
     */
    protected array $currencies = [];
    /**
     * @var Currency
     */
    protected Currency $mainCurrency;

    /**
     * @param Currency $currency
     * @return $this
     * @throws CurrencyExistException
     * @throws CurrencyNotExistException
     */
    public function addCurrency(Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === true) {
            throw new CurrencyExistException(
                'The ' . $currency->name . ' currency has already been added to the account'
            );
        }

        $this->currencies[$currency->name] = new Funds($currency);

        if ($this->mainCurrencyIsSet() === false) {
            $this->setMainCurrency($currency);
        }

        return $this;
    }

    /**
     * @param Currency $currency
     * @return bool
     */
    protected function checkCurrencyExist(Currency $currency): bool
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
     * @param Currency $currency
     * @return $this
     * @throws CurrencyNotExistException
     */
    public function setMainCurrency(Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }

        $this->mainCurrency = $currency;
        return $this;
    }

    /**
     * @return Currency
     * @throws MainCurrencyIsNotSetException
     */
    public function getMainCurrency(): Currency
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
        return array_keys($this->currencies);
    }

    /**
     * @param Funds $funds
     * @return $this
     * @throws CurrencyNotExistException
     */
    public function replenishBalance(Funds $funds): static
    {
        if ($this->checkCurrencyExist($funds->getCurrency()) === false) {
            throw new CurrencyNotExistException();
        }

        $currentFunds = $this->currencies[$funds->getCurrency()->name];
        $this->currencies[$funds->getCurrency()->name] = $currentFunds->addAmount($funds->getAmount());

        return $this;
    }

    /**
     * @param Currency|null $currency
     * @return string
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     */
    public function getBalance(Currency $currency = null): string
    {
        if ($this->currencies === []) {
            throw new CurrencyNotExistException();
        }

        if ($currency === null) {
            $currency = $this->getMainCurrency();
            $funds = $this->currencies[$currency->name];
        } elseif ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        } else {
            $funds = $this->currencies[$currency->name];
        }

        return $funds->getAmount() . ' ' . $currency->name;
    }

    /**
     * @param Funds $funds
     * @return $this
     * @throws CurrencyNotExistException
     * @throws NotEnoughFundsException
     */
    public function withdrawBalance(Funds $funds): static
    {
        if ($this->checkCurrencyExist($funds->getCurrency()) === false) {
            throw new CurrencyNotExistException();
        }

        $currentFunds = $this->currencies[$funds->getCurrency()->name];
        if ($funds->getAmount() > $currentFunds->getAmount()) {
            throw new NotEnoughFundsException();
        }

        $this->currencies[$funds->getCurrency()->name] = $currentFunds->subAmount($funds->getAmount());
        return $this;
    }
}