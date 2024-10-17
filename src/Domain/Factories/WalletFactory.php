<?php

namespace App\Domain\Factories;

use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\NegativeBalanceException;
use DateTimeImmutable;

class WalletFactory
{
    public function __construct(
        private readonly UuidGenerator $uuidGenerator,
    ) {
    }

    /**
     * @throws NegativeBalanceException
     */
    public function create(?string $id, int $balance, string $userId, ?DateTimeImmutable $createdAt = null): Wallet
    {
        $id = $id ?? $this->uuidGenerator->generateAsString();

        return new Wallet($id, $balance, $userId, $createdAt);
    }
}
