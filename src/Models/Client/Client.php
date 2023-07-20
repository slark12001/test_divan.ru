<?php

declare(strict_types=1);

namespace App\Models\Client;

use App\Exceptions;
use App\Models\Bank\Bank;
use App\Traits\Id;

class Client
{
    use Id;

    /**
     * @param string $name
     */
    public function __construct(
        protected string $name
    )
    {
        $this->generateId();
    }

    /**
     * @param Bank $bank
     * @return array
     * @throws Exceptions\ClientNotExistException
     */
    public function getAccountsByBank(Bank $bank): array
    {
        return $bank->getClientAccounts($this);
    }
}