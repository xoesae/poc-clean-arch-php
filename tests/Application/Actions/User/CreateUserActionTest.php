<?php

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Domain\Contracts\PasswordHasher;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use DateTimeImmutable;
use DI\Container;
use Exception;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    public function userProvider(): array
    {
        $container      = $this->getAppInstance()->getContainer();
        $passwordHasher = $container->get(PasswordHasher::class);
        $uuidGenerator  = $container->get(UuidGenerator::class);

        return [
            [
                $uuidGenerator->generateAsString(),
                'Bill Gates',
                'bill@example.com',
                $passwordHasher->hash('password'),
                '620.758.220-93',
                UserTypeEnum::COMMON,
                new DateTimeImmutable()],
            [
                $uuidGenerator->generateAsString(),
                'Steve Jobs',
                'steve@example.com',
                $passwordHasher->hash('password'),
                '658.358.790-40',
                UserTypeEnum::COMMON,
                new DateTimeImmutable()],
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
     * @return void
     * @throws Exception
     */
    public function testAction(
        string $id,
        string $name,
        string $email,
        string $password,
        string $documentNumber,
        UserTypeEnum $type,
        DateTimeImmutable $createdAt
    ) {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $user = new User($id, $name, $email, $password, $documentNumber, $type, $createdAt);

        $createUserUseCaseProphecy = $this->prophesize(CreateUserUseCase::class);
        $createUserUseCaseProphecy
            ->handle($name, $email, 'password', $documentNumber, $type->value)
            ->willReturn($user)
            ->shouldBeCalledOnce();

        $container->set(CreateUserUseCase::class, $createUserUseCaseProphecy->reveal());

        $request = $this
            ->createRequest('POST', '/users')
            ->withParsedBody([
                'name'            => $name,
                'email'           => $email,
                'password'        => 'password',
                'document_number' => $documentNumber,
                'type'            => $type->value,
            ])
            ->withHeader('Content-Type', 'application/json');
        $response = $app->handle($request);

        $payload           = (string) $response->getBody();
        $expectedPayload   = new ActionPayload(201, $user);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);

        $this->assertEquals($serializedPayload, $payload);
    }
}
