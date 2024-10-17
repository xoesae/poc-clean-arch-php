<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Enums\UserTypeEnum;
use App\Domain\ValueObjects\DocumentNumber;
use DateTimeImmutable;
use JsonSerializable;

class User implements JsonSerializable
{
    private string $id;

    private string $name;

    private string $email;

    private string $password;
    private DocumentNumber $documentNumber;
    private UserTypeEnum $type;
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, string $name, string $email, string $password, string $documentNumber, UserTypeEnum $type, ?DateTimeImmutable $createdAt = null)
    {
        $this->id             = $id;
        $this->name           = $name;
        $this->email          = $email;
        $this->password       = $password;
        $this->documentNumber = new DocumentNumber($documentNumber, $type);
        $this->type           = $type;
        $this->createdAt      = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getDocumentNumber(): DocumentNumber
    {
        return $this->documentNumber;
    }

    public function getType(): UserTypeEnum
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isShopkeeper(): bool
    {
        return $this->type === UserTypeEnum::SHOPKEEPER;
    }

    /**
     * @return array<string, int|string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'password'        => $this->password,
            'document_number' => $this->documentNumber->getValue(),
            'type'            => $this->type->value,
            'created_at'      => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
