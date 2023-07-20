<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Common\Funds;
use App\Enums\Currency;

interface Balance
{
    /**
     * @param Currency $currency
     * @param Funds $funds
     */
    public function replenishBalance(\App\Enums\Currency $currency, Funds $funds);

    /**
     * @param Currency|null $currency
     * @param bool $withCurrency
     * @return float|string
     */
    public function getBalance(Currency $currency = null, bool $withCurrency = false): float|string;

    /**
     * @param Currency $currency
     * @param float $amount
     * @return Funds
     */
    public function withdrawBalance(Currency $currency, float $amount): Funds;
}