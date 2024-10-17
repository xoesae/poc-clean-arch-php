<?php

namespace Tests\Application\UseCases\Wallet;

use App\Application\UseCases\Wallet\CreateWalletUseCase;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Prophecy\Argument;
use Tests\TestCase;

class CreateWalletUseCaseTest extends TestCase
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NegativeBalanceException
     * @throws UserNotFoundException
     */
    public function testCreateWallet()
    {
        /** @var Container $container */
        $container     = $this->getAppInstance()->getContainer();
        $uuidGenerator = $container->get(UuidGenerator::class);
        $userId        = $uuidGenerator->generateAsString();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->userExistsById($userId)
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        /** @var Wallet $anyWallet */
        $anyWallet = Argument::any();
        $walletRepositoryProphecy
            ->create($anyWallet)
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());

        $useCase = $container->get(CreateWalletUseCase::class);
        $wallet  = $useCase->handle($userId);

        $this->assertEquals(0, $wallet->getBalance());
        $this->assertEquals($userId, $wallet->getUserId());
        $this->assertNotEmpty($wallet->getId());
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NegativeBalanceException
     * @throws UserNotFoundException
     */
    public function testCreateWalletWithoutExistentUser()
    {
        $this->expectException(UserNotFoundException::class);

        /** @var Container $container */
        $container     = $this->getAppInstance()->getContainer();
        $uuidGenerator = $container->get(UuidGenerator::class);
        $userId        = $uuidGenerator->generateAsString();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->userExistsById($userId)
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        /** @var Wallet $anyWallet */
        $anyWallet = Argument::any();
        $walletRepositoryProphecy
            ->create($anyWallet)
            ->shouldNotBeCalled();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());

        $useCase = $container->get(CreateWalletUseCase::class);
        $useCase->handle($userId);
    }
}
