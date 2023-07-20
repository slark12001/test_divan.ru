<?php

namespace App\Tests\Traits;

use App\Enums\Currency;
use App\Exceptions\ClientIsExistException;
use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\RateCurrencyNotExistException;
use App\Models\Accounts\DebitAccount;
use App\Models\Bank\Bank;
use App\Models\Client\Client;

trait Helper
{
    public DebitAccount $account;
    public static Bank $bank;

    public static function setUpBeforeClass(): void
    {
        self::$bank = Bank::getInstance();

        self::setUpDefaultParamsBank();
    }

    protected static function setUpDefaultParamsBank()
    {
        if (self::$bank->checkCurrencyExist(Currency::RUB) === false) {
            self::$bank->addCurrency(Currency::RUB);
        }
        if (self::$bank->checkCurrencyExist(Currency::USD) === false) {
            self::$bank->addCurrency(Currency::USD);
        }
        if (self::$bank->checkCurrencyExist(Currency::EUR) === false) {
            self::$bank->addCurrency(Currency::EUR);
        }

        self::$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::RUB, 80);
        self::$bank->getExchangerRateCurrency()->setRate(Currency::USD, Currency::RUB, 70);
        self::$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::USD, 1);
    }

    /**
     * @throws CurrencyExistException
     * @throws RateCurrencyNotExistException
     * @throws ClientIsExistException
     * @throws CurrencyNotExistException
     */
    public function setUp(): void
    {
        $this->account = self::$bank->createAccount(new Client('Vlad'));

        $this->account->addCurrency(Currency::RUB);
        $this->account->addCurrency(Currency::USD);
        $this->account->addCurrency(Currency::EUR);
        $this->account->setMainCurrency(Currency::RUB);

        $this->account->replenishBalance(Currency::RUB, new \App\Common\Funds(Currency::RUB, 1000));
        $this->account->replenishBalance(Currency::EUR, new \App\Common\Funds(Currency::EUR, 50));
        $this->account->replenishBalance(Currency::USD, new \App\Common\Funds(Currency::USD, 50));
    }

    public function tearDown(): void
    {
        self::setUpDefaultParamsBank();
    }
}