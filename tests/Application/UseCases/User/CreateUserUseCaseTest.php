<?php

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\Wallet\CreateWalletUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\InvalidUserTypeException;
use App\Domain\Persistence\UserRepository;
use App\Domain\ValueObjects\DocumentNumber;
use App\Infrastructure\Services\PasswordHasher;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Prophecy\Argument;
use Tests\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function userProvider(): array
    {
        return [
            ['Bill Gates', 'bill@example.com', 'password', '620.758.220-93', UserTypeEnum::COMMON],
            ['Steve Jobs', 'steve@example.com', 'password', '658.358.790-40', UserTypeEnum::COMMON],
            ['Mark Zuckerberg', 'mark@example.com', 'password', '05.503.537/0001-98', UserTypeEnum::SHOPKEEPER],
        ];
    }

    /**
     * @dataProvider userProvider
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $documentNumber
     * @param UserTypeEnum $type
     * @throws DependencyException|NotFoundException|Exception
     */
    public function testCreateUser(
        string $name,
        string $email,
        string $password,
        string $documentNumber,
        UserTypeEnum $type
    ) {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        /** @var User $anyUser */
        $anyUser = Argument::any();
        $userRepositoryProphecy
            ->create($anyUser)
            ->willReturn(true)
            ->shouldBeCalledOnce();

        /** @var DocumentNumber $anyDocumentNumber */
        $anyDocumentNumber = Argument::any();
        $userRepositoryProphecy
            ->userExistsByDocumentNumber($anyDocumentNumber)
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $createWalletProphecy = $this->prophesize(CreateWalletUseCase::class);
        $createWalletProphecy
            ->handle(Argument::any())
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(CreateWalletUseCase::class, $createWalletProphecy->reveal());

        $useCase = $container->get(CreateUserUseCase::class);
        $user    = $useCase->handle($name, $email, $password, $documentNumber, $type->value);

        $this->assertTrue($container->get(PasswordHasher::class)->verify($password, $user->getPassword()));
        $this->assertNotEmpty($user->getId());
    }

    /**
     * @dataProvider userProvider
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $documentNumber
     * @throws DependencyException|NotFoundException|Exception
     */
    public function testCreateUserWithInvalidType(
        string $name,
        string $email,
        string $password,
        string $documentNumber,
    ) {
        $this->expectException(InvalidUserTypeException::class);

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        /** @var User $anyUser */
        $anyUser = Argument::any();
        $userRepositoryProphecy
            ->create($anyUser)
            ->shouldNotBeCalled();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $useCase = $container->get(CreateUserUseCase::class);
        $useCase->handle($name, $email, $password, $documentNumber, 'INVALID_TYPE');
    }
}
