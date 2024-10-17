<?php

namespace App\Domain\Contracts;

interface PasswordHasher
{
    public function hash(string $password): string;
    public function verify(string $password, string $hashedPassword): bool;
}
