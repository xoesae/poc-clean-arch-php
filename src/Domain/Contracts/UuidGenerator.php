<?php

namespace App\Domain\Contracts;

interface UuidGenerator
{
    public function generateAsString(): string;
}
