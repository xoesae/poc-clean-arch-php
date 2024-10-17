<?php

declare(strict_types=1);

namespace Domain\Entities;

use App\Domain\Contracts\PasswordHasher;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\ValueObjects\DocumentNumber;
use DateTimeImmutable;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function userProvider(): array
    {
        $passwordHasher = $this->getAppInstance()->getContainer()->get(PasswordHasher::class);
        $uuidGenerator  = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        return [
            [
                $uuidGenerator->generateAsString(),
                'Bill Gates',
                'bill@example.com',
                $passwordHasher->hash('password'),
                '620.758.220-93',
                UserTypeEnum::COMMON,
                new DateTimeImmutable()
            ],
            [
                $uuidGenerator->generateAsString(),
                'Steve Jobs',
                'steve@example.com',
                $passwordHasher->hash('password'),
                '658.358.790-40',
                UserTypeEnum::COMMON,
                new DateTimeImmutable()
            ],
            [
                $uuidGenerator->generateAsString(),
                'Mark Zuckerberg',
                'mark@example.com',
                $passwordHasher->hash('password'),
                '05.503.537/0001-98',
                UserTypeEnum::SHOPKEEPER,
                new DateTimeImmutable()
            ],
        ];
    }

    /**
     * @dataProvider userProvider
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $documentNumber
     * @param UserTypeEnum $type
     * @param DateTimeImmutable $createdAt
     */
    public function testGetters(
        string $id,
        string $name,
        string $email,
        string $password,
        string $documentNumber,
        UserTypeEnum $type,
        DateTimeImmutable $createdAt
    ) {
        $user = new User($id, $name, $email, $password, $documentNumber, $type, $createdAt);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(new DocumentNumber($documentNumber, $type), $user->getDocumentNumber());
        $this->assertEquals($type, $user->getType());
        $this->assertEquals($createdAt->format('Y-m-d H:i:s'), $user->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider userProvider
     * @param string $id
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $documentNumber
     * @param UserTypeEnum $type
     * @param DateTimeImmutable $createdAt
     */
    public function testJsonSerialize(
        string $id,
        string $name,
        string $email,
        string $password,
        string $documentNumber,
        UserTypeEnum $type,
        DateTimeImmutable $createdAt
    ) {
        $user = new User($id, $name, $email, $password, $documentNumber, $type, $createdAt);

        $expectedPayload = json_encode([
            'id'              => $id,
            'name'            => $name,
            'email'           => $email,
            'password'        => $password,
            'document_number' => (string) (new DocumentNumber($documentNumber, $type)),
            'type'            => $type->value,
            'created_at'      => $createdAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertEquals($expectedPayload, json_encode($user));
    }
}
