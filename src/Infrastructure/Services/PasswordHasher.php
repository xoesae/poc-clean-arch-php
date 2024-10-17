<?php

namespace App\Infrastructure\Services;

use App\Domain\Contracts\PasswordHasher as PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
