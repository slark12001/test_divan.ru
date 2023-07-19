<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;
use App\Exceptions\NotEnoughFundsException;
use App\Interfaces\MainCurrency;

class Account implements \App\Interfaces\Currency, MainCurrency
{
    use \App\Traits\Currency {
        addCurrency as addCurrencyTrait;
        addCurrency as protected;
        removeCurrency as removeCurrencyTrait;
        removeCurrency as protected;
    }

    protected string $id;
    /**
     * @var Funds[]
     */
    protected array $funds = [];
    public function __construct(
        protected readonly Client $client,
        protected readonly Bank $bank
    )
    {
        $this->id = uniqid(more_entropy: true);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Currency $currency
     * @return void
     * @throws CannotDeleteMainCurrencyException
     * @throws CurrencyNotExistException
     */
    public function removeCurrency(Currency $currency): void
    {
        $this->removeCurrencyTrait($currency);

        $currencyFunds = $this->funds[$currency->name];
        if ($currencyFunds->getAmount() > 0) {
            $this->replenishBalance($this->mainCurrency, $currencyFunds);
        }
    }

    public function addCurrency(\App\Currency $currency)
    {
        $this->addCurrencyTrait($currency);
        $this->funds[$currency->name] = new Funds($currency, 0);
    }

    /**
     * @param Currency $currency
     * @param Funds $funds
     * @return $this
     * @throws CurrencyNotExistException
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
     * @param Currency|null $currency
     * @param bool $withCurrency
     * @return float|string
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     */
    public function getBalance(Currency $currency = null, bool $withCurrency = false): float|string
    {
        if ($this->currencies === []) {
            throw new CurrencyNotExistException();
        }

        if ($currency === null) {
            $currency = $this->getMainCurrency();
            $funds = $this->funds[$currency->name];
        } elseif ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        } else {
            $funds = $this->funds[$currency->name];
        }
        $amount = $funds->getAmount();
        return $withCurrency ? $amount . ' ' . $currency->name : $amount;
    }

    /**
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