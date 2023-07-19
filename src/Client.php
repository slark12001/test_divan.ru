<?php

namespace App;

class Client
{
    protected string $id;

    public function __construct(
        protected string $name
    )
    {
        $this->id = uniqid(more_entropy: true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAccountsByBank(Bank $bank): array
    {
        return $bank->getClientAccounts($this);
    }
}