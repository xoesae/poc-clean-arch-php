<?php

namespace App\Infrastructure\Services;

use App\Domain\Contracts\UuidGenerator;
use Ramsey\Uuid\Uuid;

class Uuid7Generator implements UuidGenerator
{
    public function generateAsString(): string
    {
        $uuid = Uuid::uuid7();

        return $uuid->toString();
    }
}
