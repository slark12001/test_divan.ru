<?php

namespace App\Interfaces;

interface MainCurrency
{
    public function setMainCurrency(\App\Currency $currency);

    public function getMainCurrency(): \App\Currency;
}