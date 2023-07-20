<?php

namespace App\Tests\Models\Bank;

use App\Common\Funds;
use App\Enums\Currency;
use App\Exceptions\CannotDeleteMainCurrencyException;
use App\Models\Bank\Bank;
use App\Models\Client\Client;
use PHPUnit\Framework\TestCase;
use App\Tests\Traits\Helper;

class BankTest extends TestCase
{
    use Helper;

    public function testRemoveMainCurrency()
    {
        $this->expectExceptionObject(new CannotDeleteMainCurrencyException());
        self::$bank->removeCurrency(self::$bank->getMainCurrency());
    }

    public function testRemoveCurrency()
    {
        self::$bank->removeCurrency(Currency::EUR);
        $this->assertEquals([Currency::RUB->name, Currency::USD->name],self::$bank->listAvailableCurrencies());
    }

    public function testCreateAccount()
    {
        $account = self::$bank->createAccount(new Client('Vlad'));
        $this->assertIsObject($account);
    }

    public function testEditRate()
    {
        self::$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::RUB, 150);
        $balance = $this->account->getBalance(Currency::EUR);
        $funds = $this->account->withdrawBalance(Currency::EUR, $balance);
        $convert = self::$bank->getExchangerRateCurrency()->convert($funds->getCurrency(), Currency::RUB, $funds->getAmount());
        $this->assertEquals(50 * 150, $convert->getAmount());
    }
}
