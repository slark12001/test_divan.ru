<?php

namespace App\Tests\Models\Accounts;

use App\Common\Funds;
use App\Enums\Currency;
use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Exceptions\ClientIsExistException;
use App\Exceptions\CurrencyExistException;
use App\Exceptions\CurrencyNotExistException;
use App\Exceptions\MainCurrencyIsNotSetException;
use App\Exceptions\NeedWithdrawFundsException;
use App\Exceptions\NotEnoughFundsException;
use App\Exceptions\RateCurrencyNotExistException;
use App\Models\Accounts\Account;
use App\Models\Accounts\DebitAccount;
use App\Models\Bank\Bank;
use App\Models\Client\Client;
use PHPUnit\Framework\TestCase;
use App\Tests\Traits\Helper;

class DebitAccountTest extends TestCase
{
    use Helper;
    /**
     * @return void
     * @throws CurrencyNotExistException
     * @throws NotEnoughFundsException
     */
    public function testWithdrawBalance(): void
    {
        $funds = $this->account->withdrawBalance(Currency::RUB, 1000);
        $this->assertEquals(new Funds(Currency::RUB, 1000), $funds);
    }

    /**
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     */
    public function testGetBalance(): void
    {
        $balanceWithCurrency = $this->account->getBalance(withCurrency: true);
        $balanceWithoutCurrency = $this->account->getBalance();
        $balanceConcreteCurrency = $this->account->getBalance(Currency::USD);
        $this->assertEquals('1000 ' . Currency::RUB->name, $balanceWithCurrency);
        $this->assertEquals(1000, $balanceWithoutCurrency);
        $this->assertEquals(50, $balanceConcreteCurrency);
    }

    /**
     * @throws NotEnoughFundsException
     * @throws CurrencyNotExistException
     * @throws NeedWithdrawFundsException
     * @throws MainCurrencyIsNotSetException
     * @throws CannotDeleteMainCurrencyException
     */
    public function testGetBalanceWithRemovedCurrency(): void
    {
        $this->withdrawAllFunds(Currency::USD);
        $this->account->removeCurrency(Currency::USD);
        $this->expectExceptionObject(new CurrencyNotExistException());
        $this->account->getBalance(Currency::USD);
    }


    /**
     * @throws NotEnoughFundsException
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     */
    protected function withdrawAllFunds(Currency $currency): void
    {
        $balance = $this->account->getBalance($currency);
        $this->account->withdrawBalance($currency, $balance);
    }

    /**
     * @throws MainCurrencyIsNotSetException
     * @throws NeedWithdrawFundsException
     * @throws CurrencyNotExistException
     * @throws CannotDeleteMainCurrencyException|NotEnoughFundsException
     */
    public function testRemoveCurrency(): void
    {
        //Correct remove currency
        $this->withdrawAllFunds(Currency::USD);
        $this->account->removeCurrency(Currency::USD);
        $this->assertEquals([Currency::RUB->name, Currency::EUR->name], $this->account->listAvailableCurrencies());
    }

    /**
     * @throws CurrencyNotExistException
     * @throws CannotDeleteMainCurrencyException
     * @throws NeedWithdrawFundsException
     */
    public function testRemoveCurrencyWithoutWithdrawFunds(): void
    {
        $this->expectExceptionObject(new NeedWithdrawFundsException());
        $this->account->removeCurrency(Currency::USD);
    }

    /**
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     * @throws CannotDeleteMainCurrencyException
     * @throws NotEnoughFundsException
     * @throws NeedWithdrawFundsException
     */
    public function testRemoveNotExistCurrency(): void
    {
        $this->withdrawAllFunds(Currency::USD);
        $this->account->removeCurrency(Currency::USD);

        $this->expectExceptionObject(new CurrencyNotExistException());
        $this->account->removeCurrency(Currency::USD);
    }

    /**
     * @throws CurrencyNotExistException
     * @throws NeedWithdrawFundsException
     * @throws MainCurrencyIsNotSetException
     * @throws CannotDeleteMainCurrencyException
     */
    public function testRemoveMainCurrency(): void
    {
        $this->expectExceptionObject(new CannotDeleteMainCurrencyException());
        $this->account->removeCurrency($this->account->getMainCurrency());
    }

    /**
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     * @throws RateCurrencyNotExistException
     */
    public function testReplenishBalance(): void
    {
        //Correct replenish
        $this->account->replenishBalance(Currency::RUB, new Funds(Currency::RUB, 1000));
        $this->assertEquals(2000, $this->account->getBalance(Currency::RUB));
    }

    /**
     * @throws NotEnoughFundsException
     * @throws CurrencyNotExistException
     * @throws NeedWithdrawFundsException
     * @throws MainCurrencyIsNotSetException
     * @throws CannotDeleteMainCurrencyException
     * @throws RateCurrencyNotExistException
     */
    public function testReplenishNotExistCurrency(): void
    {
        $this->withdrawAllFunds(Currency::USD);
        $this->account->removeCurrency(Currency::USD);
        $this->expectExceptionObject(new CurrencyNotExistException());
        $this->account->replenishBalance(Currency::USD, new Funds(Currency::USD, 50));
    }

    /**
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     * @throws RateCurrencyNotExistException
     */
    public function testReplenishWithAnotherCurrency(): void
    {
        $this->account->replenishBalance(Currency::RUB, new Funds(Currency::EUR, 3));
        $this->assertEquals(1240, $this->account->getBalance(Currency::RUB));
    }

    /**
     * @throws CurrencyNotExistException
     * @throws CurrencyExistException
     * @throws ClientIsExistException
     */
    public function testAddCurrency(): void
    {
        $account = self::$bank->createAccount(new Client('Test'));
        $account->addCurrency(Currency::RUB);
        $this->assertEquals([Currency::RUB->name], $account->listAvailableCurrencies());
    }

    /**
     * @throws CurrencyNotExistException
     * @throws CurrencyExistException
     */
    public function testAddExistCurrency(): void
    {
        $this->expectExceptionObject(
            new CurrencyExistException('The ' . Currency::RUB->name . ' currency has already been added')
        );
        $this->account->addCurrency(Currency::RUB);
    }

    /**
     * @return void
     * @throws CannotDeleteMainCurrencyException
     * @throws ClientIsExistException
     * @throws CurrencyExistException
     * @throws CurrencyNotExistException
     * @throws MainCurrencyIsNotSetException
     * @throws NeedWithdrawFundsException
     * @throws NotEnoughFundsException
     * @throws RateCurrencyNotExistException
     */
    public function testAddNotExistInBankCurrencylCase(): void
    {
        $account = self::$bank->createAccount(new Client('Test'));
        self::$bank->removeCurrency(Currency::EUR);
        $this->expectExceptionObject(new CurrencyNotExistException('There is no such currency in the bank'));
        $account->addCurrency(Currency::EUR);
    }
}
