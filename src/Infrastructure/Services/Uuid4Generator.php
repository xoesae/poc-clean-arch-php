<?php

namespace App\Infrastructure\Services;

use App\Domain\Contracts\UuidGenerator;
use Ramsey\Uuid\Uuid;

class Uuid4Generator implements UuidGenerator
{
    public function generateAsString(): string
    {
        $uuid = Uuid::uuid4();

        return $uuid->toString();
    }
}
