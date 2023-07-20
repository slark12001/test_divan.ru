<?php

declare(strict_types=1);

namespace App\Models\Accounts;

use App\Common\Funds;
use App\Enums\Currency;
use App\Exceptions;
use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;
use App\Exceptions\NotEnoughFundsException;
use App\Interfaces\MainCurrency;
use App\Traits\Id;

class DebitAccount extends Account implements MainCurrency
{
    use \App\Traits\Currency {
        addCurrency as addCurrencyTrait;
        addCurrency as protected;
    }
    use Id;

    /**
     * List of funds for which currencies have been added
     * Example: USD => Funds(USD, 100)
     * @var Funds[]
     */
    protected array $funds = [];

    /**
     * Deletes a currency, if possible, and converts that currency into the main currency
     * @param Currency $currency
     * @return DebitAccount
     * @throws CannotDeleteMainCurrencyException
     * @throws CurrencyNotExistException|Exceptions\RateCurrencyNotExistException
     */
    public function removeCurrency(Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        } elseif ($this->mainCurrency === $currency) {
            throw new CannotDeleteMainCurrencyException();
        }

        $currencyFunds = $this->funds[$currency->name];
        if ($currencyFunds->getAmount() > 0) {
            $this->replenishBalance($this->mainCurrency, $currencyFunds);
        }
        $this->currencies[$currency->name] = false;
        return $this;
    }

    /**
     * Adds currency, if possible (if the bank has such a currency and this currency has not been added to the account).
     * Also creates funds for the currency
     * @param Currency $currency
     * @return $this
     * @throws CurrencyNotExistException
     * @throws Exceptions\CurrencyExistException
     */
    public function addCurrency(Currency $currency): static
    {
        if ($this->bank->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException('There is no such currency in the bank');
        }

        $this->addCurrencyTrait($currency);
        if (isset($this->funds[$currency->name]) === false) {
            $this->funds[$currency->name] = new Funds($currency, 0);
        }

        return $this;
    }

    /**
     * Replenishes the balance of the specified currency, if possible
     * @param Currency $currency
     * @param Funds $funds
     * @return $this
     * @throws CurrencyNotExistException|Exceptions\RateCurrencyNotExistException
     */
    public function replenishBalance(Currency $currency, Funds $funds): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }

        $currentFunds = $this->funds[$currency->name];
        if ($currentFunds->getCurrency() === $funds->getCurrency()) {
            $amount = $funds->getAmount();
        } else {
            $convertFunds = $this->bank
                ->getExchangerRateCurrency()
                ->convert($funds->getCurrency(), $currency, $funds->getAmount());
            $amount = $convertFunds->getAmount();
        }
        $currentFunds->addAmount($amount);

        return $this;
    }

    /**
     * Returns the balance of the base currency or in the specified currency.
     * If $withCurrency = true, it will return a string with the balance and its currency,
     * otherwise it will return the balance to float
     * @param Currency|null $currency
     * @param bool $withCurrency
     * @return float|string
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     */
    public function getBalance(Currency $currency = null, bool $withCurrency = false): float|string
    {
        if ($currency !== null
            && $this->checkCurrencyExist($currency) === false
        ) {
            throw new CurrencyNotExistException();
        }

        if ($currency === null) {
            $currency = $this->getMainCurrency();
        }
        $funds = $this->funds[$currency->name];
        $amount = $funds->getAmount();
        return $withCurrency ? $amount . ' ' . $currency->name : $amount;
    }

    /**
     * Withdraws funds from the balance, if possible, and returns the Funds object
     * @param Currency $currency
     * @param float $amount
     * @return Funds
     * @throws CurrencyNotExistException
     * @throws NotEnoughFundsException
     */
    public function withdrawBalance(Currency $currency, float $amount): Funds
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }

        $currentFunds = $this->funds[$currency->name];
        if ($amount > $currentFunds->getAmount()) {
            throw new NotEnoughFundsException();
        }

        $currentFunds->subAmount($amount);

        return new Funds($currency, $amount);
    }
}