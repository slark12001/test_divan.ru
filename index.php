<?php

use App\Enums\Currency;
use App\Models\Client\Client;

require_once __DIR__ . '/vendor/autoload.php';

function myVarDump($data) {
    var_dump($data);
    echo "<br>";
}

/**
 * @var \App\Models\Bank\Bank $bank
 */
$bank = \App\Models\Bank\Bank::getInstance();

$bank->addCurrency(Currency::RUB);
$bank->addCurrency(Currency::USD);
$bank->addCurrency(Currency::EUR);

$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::RUB, 80);
$bank->getExchangerRateCurrency()->setRate(Currency::USD, Currency::RUB, 70);
$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::USD, 1);

$account = $bank->createAccount(new Client('Vlad'));
$account->addCurrency(Currency::RUB);
$account->addCurrency(Currency::USD);
$account->addCurrency(Currency::EUR);
$account->setMainCurrency(Currency::RUB);
//myVarDump($account->listAvailableCurrencies());
$account->replenishBalance(Currency::RUB, new \App\Common\Funds(Currency::RUB, 1000));
$account->replenishBalance(Currency::EUR, new \App\Common\Funds(Currency::EUR, 50));
$account->replenishBalance(Currency::USD, new \App\Common\Funds(Currency::USD, 50));
//myVarDump([
//    $account->getBalance(Currency::RUB, true),
//    $account->getBalance(Currency::EUR, true),
//    $account->getBalance(Currency::USD, true)
//]);

$account->replenishBalance(Currency::RUB, new \App\Common\Funds(Currency::RUB, 1000));
$account->replenishBalance(Currency::EUR, new \App\Common\Funds(Currency::EUR, 50));
$account->withdrawBalance(Currency::USD, 10);
//myVarDump([
//    $account->getBalance(Currency::RUB, true),
//    $account->getBalance(Currency::EUR, true),
//    $account->getBalance(Currency::USD, true)
//]);

$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::RUB, 150);
$bank->getExchangerRateCurrency()->setRate(Currency::USD, Currency::RUB, 100);
//myVarDump($account->getBalance(withCurrency: true));
$account->setMainCurrency(Currency::EUR);
//myVarDump($account->getBalance(withCurrency: true));
$funds = $account->withdrawBalance(Currency::RUB, 1000);
$account->replenishBalance(Currency::EUR, $funds);

//myVarDump($account->getBalance(withCurrency: true));
$bank->getExchangerRateCurrency()->setRate(Currency::EUR, Currency::RUB, 120);
//myVarDump($account->getBalance(withCurrency: true));
$bank->removeCurrency(Currency::EUR);
$bank->removeCurrency(Currency::USD);

//myVarDump([
//    $account->listAvailableCurrencies(),
//    $account->getBalance(withCurrency: true)
//]);