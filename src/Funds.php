<?php

declare(strict_types=1);

namespace App;

class Funds
{
    public function __construct(
        private readonly Currency $currency,
        private float $amount = 0
    )
    {
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @param float $amount
     * @return Funds
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function addAmount(float $amount): static
    {
        $this->amount += $amount;
        return $this;
    }

    public function subAmount(float $amount): static
    {
        $this->amount -= $amount;
        return $this;
    }
}