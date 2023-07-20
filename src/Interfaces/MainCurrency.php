<?php

declare(strict_types=1);

namespace App\Interfaces;

interface MainCurrency
{
    /**
     * @param \App\Enums\Currency $currency
     * @return mixed
     */
    public function setMainCurrency(\App\Enums\Currency $currency): mixed;

    /**
     * @return \App\Enums\Currency
     */
    public function getMainCurrency(): \App\Enums\Currency;
}