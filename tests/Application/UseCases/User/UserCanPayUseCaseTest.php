<?php

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\UserCanPayUseCase;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\User;
use App\Domain\Entities\Wallet;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Exceptions\InvalidPayeeWalletException;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Exceptions\NotValidTransactionValueException;
use App\Domain\Exceptions\PaymentNotAuthorizedException;
use App\Domain\Exceptions\ShopkeeperCannotPayException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\WalletNotFoundException;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\TestCase;

class UserCanPayUseCaseTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws NegativeBalanceException
     * @throws PaymentNotAuthorizedException
     * @throws NotFoundExceptionInterface
     * @throws NotValidTransactionValueException
     * @throws ShopkeeperCannotPayException
     * @throws DependencyException
     * @throws InvalidPayeeWalletException
     * @throws InsufficientBalanceException
     * @throws NotFoundException
     */
    public function testUserCanPay()
    {
        $value         = 10_000;
        $uuidGenerator = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        $payer       = new User($uuidGenerator->generateAsString(), 'Bill Gates', 'bill@example.com', 'password', '620.758.220-93', UserTypeEnum::COMMON);
        $payerWallet = new Wallet($uuidGenerator->generateAsString(), $value, $payer->getId());

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payer->getDocumentNumber())
            ->willReturn($payer)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        $walletRepositoryProphecy
            ->findWalletByUserId($payer->getId())
            ->willReturn($payerWallet)
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());

        $useCase = $container->get(UserCanPayUseCase::class);
        $useCase->handle((string) $payer->getDocumentNumber(), $value);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws NegativeBalanceException
     * @throws PaymentNotAuthorizedException
     * @throws NotFoundExceptionInterface
     * @throws NotValidTransactionValueException
     * @throws ShopkeeperCannotPayException
     * @throws DependencyException
     * @throws InvalidPayeeWalletException
     * @throws InsufficientBalanceException
     * @throws NotFoundException
     */
    public function testShopkeeperCannotPay()
    {
        $this->expectException(ShopkeeperCannotPayException::class);

        $uuidGenerator = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);
        $payer         = new User($uuidGenerator->generateAsString(), 'Bill Gates', 'bill@example.com', 'password', '05.503.537/0001-98', UserTypeEnum::SHOPKEEPER);

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payer->getDocumentNumber())
            ->willReturn($payer)
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $useCase = $container->get(UserCanPayUseCase::class);
        $useCase->handle((string) $payer->getDocumentNumber(), 10_000);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws NegativeBalanceException
     * @throws PaymentNotAuthorizedException
     * @throws NotFoundExceptionInterface
     * @throws NotValidTransactionValueException
     * @throws ShopkeeperCannotPayException
     * @throws DependencyException
     * @throws InvalidPayeeWalletException
     * @throws InsufficientBalanceException
     * @throws NotFoundException
     */
    public function testUserWithInsufficientBalance()
    {
        $this->expectException(InsufficientBalanceException::class);

        $value         = 10_000;
        $uuidGenerator = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        $payer       = new User($uuidGenerator->generateAsString(), 'Bill Gates', 'bill@example.com', 'password', '620.758.220-93', UserTypeEnum::COMMON);
        $payerWallet = new Wallet($uuidGenerator->generateAsString(), $value, $payer->getId());

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payer->getDocumentNumber())
            ->willReturn($payer)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        $walletRepositoryProphecy
            ->findWalletByUserId($payer->getId())
            ->willReturn($payerWallet)
            ->shouldBeCalledOnce();

        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());

        $useCase = $container->get(UserCanPayUseCase::class);
        $useCase->handle((string) $payer->getDocumentNumber(), $value + 1);
    }
}
