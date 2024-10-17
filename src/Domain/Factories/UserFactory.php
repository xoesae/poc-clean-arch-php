<?php

declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\Contracts\PasswordHasher;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;

class UserFactory
{
    public function __construct(
        private readonly PasswordHasher $hasher,
        private readonly UuidGenerator $uuidGenerator,
    ) {
    }

    public function create(?string $id, string $name, string $email, string $password, string $documentNumber, string|UserTypeEnum $type, ?\DateTimeImmutable $createdAt = null): User
    {
        $id       = $id ?? $this->uuidGenerator->generateAsString();
        $password = $this->hasher->hash($password);
        $type     = ($type instanceof UserTypeEnum) ? $type : UserTypeEnum::from($type);

        return new User($id, $name, $email, $password, $documentNumber, $type, $createdAt);
    }
}
