<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Enums\TransactionStatusEnum;
use App\Domain\Exceptions\InvalidPayeeWalletException;
use App\Domain\Exceptions\NotValidTransactionValueException;
use DateTimeImmutable;
use JsonSerializable;

class Transaction implements JsonSerializable
{
    private string $id;
    private string $payerWalletId;
    private string $payeeWalletId;
    private int $value;
    private TransactionStatusEnum $status;
    private DateTimeImmutable $createdAt;

    /**
     * @throws NotValidTransactionValueException
     * @throws InvalidPayeeWalletException
     */
    public function __construct(string $id, string $payerWalletId, string $payeeWalletId, int $value, TransactionStatusEnum $status, ?DateTimeImmutable $createdAt = null)
    {
        if ($value <= 0) {
            throw new NotValidTransactionValueException();
        }

        if ($payerWalletId === $payeeWalletId) {
            throw new InvalidPayeeWalletException();
        }

        $this->id            = $id;
        $this->payerWalletId = $payerWalletId;
        $this->payeeWalletId = $payeeWalletId;
        $this->value         = $value;
        $this->status        = $status;
        $this->createdAt     = $createdAt ?? new DateTimeImmutable();
    }

    public function setStatus(TransactionStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatusEnum::PENDING;
    }

    public function isNotAuthorized(): bool
    {
        return $this->status === TransactionStatusEnum::NOT_AUTHORIZED;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPayerWalletId(): string
    {
        return $this->payerWalletId;
    }

    public function getPayeeWalletId(): string
    {
        return $this->payeeWalletId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getStatus(): TransactionStatusEnum
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id'            => $this->id,
            'payerWalletId' => $this->payerWalletId,
            'payeeWalletId' => $this->payeeWalletId,
            'value'         => $this->value,
            'status'        => $this->status->value,
            'createdAt'     => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
