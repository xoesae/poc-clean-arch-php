<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Exceptions\NegativeBalanceException;
use DateTimeImmutable;
use JsonSerializable;

class Wallet implements JsonSerializable
{
    private string $id;
    private int $balance;
    private string $userId;
    private DateTimeImmutable $createdAt;

    /**
     * @throws NegativeBalanceException
     */
    public function __construct(string $id, int $balance, string $userId, ?DateTimeImmutable $createdAt = null)
    {
        if ($balance < 0) {
            throw new NegativeBalanceException();
        }
        $this->id        = $id;
        $this->balance   = $balance;
        $this->userId    = $userId;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @throws NegativeBalanceException|InsufficientBalanceException
     */
    public function pay(int $value): void
    {
        if ($value < 0) {
            throw new NegativeBalanceException();
        }

        if ($value > $this->balance) {
            throw new InsufficientBalanceException();
        }

        $this->balance -= $value;
    }

    /**
     * @throws NegativeBalanceException
     */
    public function receive(int $value): void
    {
        if ($value < 0) {
            throw new NegativeBalanceException();
        }

        $this->balance += $value;
    }

    /**
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'balance'    => $this->balance,
            'user_id'    => $this->userId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
