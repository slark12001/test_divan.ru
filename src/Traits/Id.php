<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Designed to get rid of the code when working with id
 */
trait Id
{
    /**
     * Unique id
     * @var string
     */
    protected string $id = '';

    /**
     * Return id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Generate unique id
     * @return void
     */
    protected function generateId(): void
    {
        $this->id = uniqid(more_entropy: true);
    }
}