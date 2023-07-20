<?php

declare(strict_types=1);

namespace App\Models\Accounts;

use App\Interfaces\Balance;
use App\Interfaces\Currency;
use App\Models\Bank\Bank;
use App\Models\Client\Client;
use App\Traits\Id;

abstract class Account implements Currency, Balance
{
    use Id;

    public function __construct(
        protected readonly Client $client,
        protected readonly Bank   $bank
    )
    {
        $this->generateId();
    }
}