<?php

namespace App\Interfaces;

interface Currency
{
    public function addCurrency(\App\Currency $currency);

    public function removeCurrency(\App\Currency $currency);

}