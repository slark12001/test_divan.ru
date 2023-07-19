<?php

declare(strict_types=1);

namespace App;

use App\Common\Singleton;
use App\Exceptions\ClientIsExistException;
use App\Exceptions\ClientNotExistException;
use App\Interfaces\MainCurrency;

class Bank extends Singleton implements \App\Interfaces\Currency, MainCurrency
{
    use \App\Traits\Currency {
        removeCurrency as removeCurrencyTrait;
        removeCurrency as protected;
    }

    /**
     * @var Client[]
     */
    protected array $clients = [];
    protected array $clientsAccounts = [];
    /**
     * @var Account[]
     */
    protected array $accounts = [];
    protected array $currencies = [];


    protected function __construct(
        protected ExchangerRateCurrency $exchangerRateCurrency = new ExchangerRateCurrency()
    )
    {
        parent::__construct();
    }

    /**
     * @param ExchangerRateCurrency $exchangerRateCurrency
     * @return Bank
     */
    public function setExchangerRateCurrency(ExchangerRateCurrency $exchangerRateCurrency): static
    {
        $this->exchangerRateCurrency = $exchangerRateCurrency;
        return $this;
    }

    /**
     * @param Client $client
     * @return Account
     * @throws ClientIsExistException
     */
    public function createAccount(Client $client): Account
    {
        $account = new Account($client, $this);

        if (isset($this->clients[$client->getId()]) === true) {
            throw new ClientIsExistException();
        }

        $this->clients[$client->getId()] = $client;
        $this->clientsAccounts[$client->getId()][] = $account->getId();
        $this->accounts[] = $account;

        return $account;
    }

    /**
     * @param Client $client
     * @return array
     * @throws ClientNotExistException
     */
    public function getClientAccounts(Client $client): array
    {
        if (isset($this->clients[$client->getId()]) === false) {
            throw new ClientNotExistException();
        }
        $clientAccountsIds = $this->clientsAccounts[$client->getId()];
        $accounts = [];
        for ($i = 0; $i < count($clientAccountsIds); $i++) {
            $accounts[] = $this->accounts[$clientAccountsIds[$i]];
        }
        return $accounts;
    }

    /**
     * @return array
     */
    public function getAccounts(): array
    {
        return array_values($this->clientsAccounts);
    }

    /**
     * @return array
     */
    public function getClients(): array
    {
        return array_values($this->clients);
    }

    /**
     * @return ExchangerRateCurrency
     */
    public function getExchangerRateCurrency(): ExchangerRateCurrency
    {
        return $this->exchangerRateCurrency;
    }

    /**
     * @param Currency $currency
     * @return void
     * @throws Exceptions\CannotDeleteMainCurrencyException
     * @throws Exceptions\CurrencyExistException
     * @throws Exceptions\CurrencyNotExistException
     * @throws Exceptions\MainCurrencyIsNotSetException
     */
    public function removeCurrency(\App\Currency $currency): void
    {
        $this->removeCurrencyTrait($currency);

        for ($i = 0; $i < count($this->accounts); $i++) {
            $account = $this->accounts[$i];
            if ($account->getMainCurrency() === $currency
                && $account->checkCurrencyExist($this->getMainCurrency()) === false
            ) {
                $account->addCurrency($this->getMainCurrency());
            }
            $account->setMainCurrency($this->getMainCurrency());
            $account->removeCurrency($currency);
        }
    }
}