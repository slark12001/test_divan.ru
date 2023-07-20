<?php

declare(strict_types=1);

namespace App\Models\Bank;

use App\Common\Singleton;
use App\Enums\Currency;
use App\Exceptions;
use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Exceptions\ClientIsExistException;
use App\Exceptions\ClientNotExistException;
use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;
use App\Exceptions\NotEnoughFundsException;
use App\Interfaces\MainCurrency;
use App\Models\Accounts\DebitAccount;
use App\Models\Client\Client;

class Bank extends Singleton implements \App\Interfaces\Currency, MainCurrency
{
    use \App\Traits\Currency;

    /**
     * @var Client[]
     */
    protected array $clients = [];
    protected array $clientsAccounts = [];
    /**
     * @var DebitAccount[]
     */
    protected array $accounts = [];
    protected array $currencies = [];
    protected ExchangerRateCurrency $exchangerRateCurrency;

    protected function __construct()
    {
        $this->exchangerRateCurrency = new ExchangerRateCurrency($this);
        parent::__construct();
    }

    /**
     * @param Client $client
     * @return DebitAccount
     * @throws ClientIsExistException
     */
    public function createAccount(Client $client): DebitAccount
    {
        $account = new DebitAccount($client, $this);

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
     * @return DebitAccount[]
     */
    public function getAccounts(): array
    {
        return array_values($this->accounts);
    }

    /**
     * @return Client[]
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
     * @return Bank
     * @throws CannotDeleteMainCurrencyException
     * @throws CurrencyExistException
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException|Exceptions\NeedWithdrawFundsException
     * @throws NotEnoughFundsException|Exceptions\RateCurrencyNotExistException
     */
    public function removeCurrency(Currency $currency): static
    {
        if ($this->checkCurrencyExist($currency) === false) {
            throw new CurrencyNotExistException();
        }
        if ($this->mainCurrency === $currency) {
            throw new CannotDeleteMainCurrencyException();
        }

        for ($i = 0; $i < count($this->accounts); $i++) {
            $account = $this->accounts[$i];
            if ($account->checkCurrencyExist($currency) === true) {
                if ($account->checkCurrencyExist($this->getMainCurrency()) === false) {
                    $account->addCurrency($this->getMainCurrency());
                }
                $account->setMainCurrency($this->getMainCurrency());
                $balance = $account->getBalance($currency);
                $funds = $account->withdrawBalance($currency, $balance);
                $account->replenishBalance($account->getMainCurrency(), $funds);
                $account->removeCurrency($currency);
            }
        }
        $this->currencies[$currency->name] = false;
        return $this;
    }
}