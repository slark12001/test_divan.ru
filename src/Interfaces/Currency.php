<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Designed to implement work with currencies in entities
 */
interface Currency
{
    /**
     * Adds currency
     * @param \App\Enums\Currency $currency
     */
    public function addCurrency(\App\Enums\Currency $currency);

    /**
     * Remove currency
     * @param \App\Enums\Currency $currency
     */
    public function removeCurrency(\App\Enums\Currency $currency);

}