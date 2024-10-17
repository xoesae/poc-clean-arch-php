<?php

namespace Tests\Application\UseCases;

use App\Application\Contracts\AuthorizePayment;
use App\Application\UseCases\PayUserUseCase;
use App\Application\UseCases\User\UserCanPayUseCase;
use App\Domain\Contracts\PasswordHasher;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Transaction;
use App\Domain\Entities\User;
use App\Domain\Entities\Wallet;
use App\Domain\Enums\TransactionStatusEnum;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Exceptions\InvalidPayeeWalletException;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Exceptions\NotValidTransactionValueException;
use App\Domain\Exceptions\PaymentNotAuthorizedException;
use App\Domain\Exceptions\ShopkeeperCannotPayException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\WalletNotFoundException;
use App\Domain\Persistence\TransactionRepository;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use DateTimeImmutable;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Prophecy\Argument;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\TestCase;

class PayUserUseCaseTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NegativeBalanceException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws InsufficientBalanceException
     * @throws InvalidPayeeWalletException
     * @throws NotValidTransactionValueException
     * @throws PaymentNotAuthorizedException
     * @throws ShopkeeperCannotPayException
     */
    public function testValidPayment()
    {
        $value          = 10_000;
        $passwordHasher = $this->getAppInstance()->getContainer()->get(PasswordHasher::class);
        $uuidGenerator  = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        $payer       = new User($uuidGenerator->generateAsString(), 'Bill Gates', 'bill@example.com', $passwordHasher->hash('password'), '620.758.220-93', UserTypeEnum::COMMON, new DateTimeImmutable());
        $payerWallet = new Wallet($uuidGenerator->generateAsString(), 10000, $payer->getId(), new DateTimeImmutable());
        $payee       = new User($uuidGenerator->generateAsString(), 'Steve Jobs', 'steve@example.com', $passwordHasher->hash('password'), '658.358.790-40', UserTypeEnum::COMMON, new DateTimeImmutable());
        $payeeWallet = new Wallet($uuidGenerator->generateAsString(), 0, $payee->getId(), new DateTimeImmutable());

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userCanPayProphecy = $this->prophesize(UserCanPayUseCase::class);
        $userCanPayProphecy
            ->handle((string) $payer->getDocumentNumber(), $value)
            ->shouldBeCalledOnce();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payer->getDocumentNumber())
            ->willReturn($payer)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payee->getDocumentNumber())
            ->willReturn($payee)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        $walletRepositoryProphecy
            ->findWalletByUserId($payer->getId())
            ->willReturn($payerWallet)
            ->shouldBeCalledOnce();
        $walletRepositoryProphecy
            ->findWalletByUserId($payee->getId())
            ->willReturn($payeeWallet)
            ->shouldBeCalledOnce();

        $payeeWallet->receive($value);

        /** @var Wallet $anyWallet */
        $anyWallet = Argument::any();
        $walletRepositoryProphecy
            ->update(Argument::any(), $anyWallet)
            ->shouldBeCalled();

        $transactionRepositoryProphecy = $this->prophesize(TransactionRepository::class);
        /** @var Transaction $anyTransaction */
        $anyTransaction = Argument::any();
        $transactionRepositoryProphecy
            ->create($anyTransaction)
            ->shouldBeCalledOnce();

        $authorizePaymentService = $this->prophesize(AuthorizePayment::class);
        $authorizePaymentService
            ->isAuthorized()
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $container->set(UserCanPayUseCase::class, $userCanPayProphecy->reveal());
        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());
        $container->set(TransactionRepository::class, $transactionRepositoryProphecy->reveal());
        $container->set(AuthorizePayment::class, $authorizePaymentService->reveal());

        $useCase     = $container->get(PayUserUseCase::class);
        $transaction = $useCase->handle((string) $payer->getDocumentNumber(), (string) $payee->getDocumentNumber(), $value);

        $this->assertEquals(TransactionStatusEnum::COMPLETED, $transaction->getStatus());
        $this->assertEquals($value, $transaction->getValue());
        $this->assertEquals($payerWallet->getId(), $transaction->getPayerWalletId());
        $this->assertEquals($payeeWallet->getId(), $transaction->getPayeeWalletId());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NegativeBalanceException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws InsufficientBalanceException
     * @throws InvalidPayeeWalletException
     * @throws NotValidTransactionValueException
     * @throws PaymentNotAuthorizedException
     * @throws ShopkeeperCannotPayException
     */
    public function testNotAuthorizedPayment()
    {
        $this->expectException(PaymentNotAuthorizedException::class);

        $value          = 10_000;
        $passwordHasher = $this->getAppInstance()->getContainer()->get(PasswordHasher::class);
        $uuidGenerator  = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        $payer       = new User($uuidGenerator->generateAsString(), 'Bill Gates', 'bill@example.com', $passwordHasher->hash('password'), '620.758.220-93', UserTypeEnum::COMMON, new DateTimeImmutable());
        $payerWallet = new Wallet($uuidGenerator->generateAsString(), 10000, $payer->getId(), new DateTimeImmutable());
        $payee       = new User($uuidGenerator->generateAsString(), 'Steve Jobs', 'steve@example.com', $passwordHasher->hash('password'), '658.358.790-40', UserTypeEnum::COMMON, new DateTimeImmutable());
        $payeeWallet = new Wallet($uuidGenerator->generateAsString(), 0, $payee->getId(), new DateTimeImmutable());

        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $userCanPayProphecy = $this->prophesize(UserCanPayUseCase::class);
        $userCanPayProphecy
            ->handle((string) $payer->getDocumentNumber(), $value)
            ->shouldBeCalledOnce();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payer->getDocumentNumber())
            ->willReturn($payer)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserByDocumentNumber($payee->getDocumentNumber())
            ->willReturn($payee)
            ->shouldBeCalledOnce();

        $walletRepositoryProphecy = $this->prophesize(WalletRepository::class);
        $walletRepositoryProphecy
            ->findWalletByUserId($payer->getId())
            ->willReturn($payerWallet)
            ->shouldBeCalledOnce();
        $walletRepositoryProphecy
            ->findWalletByUserId($payee->getId())
            ->willReturn($payeeWallet)
            ->shouldBeCalledOnce();

        $payeeWallet->receive($value);

        /** @var Wallet $anyWallet */
        $anyWallet = Argument::any();
        $walletRepositoryProphecy
            ->update(Argument::any(), $anyWallet)
            ->shouldNotBeCalled();

        $transactionRepositoryProphecy = $this->prophesize(TransactionRepository::class);
        /** @var Transaction $anyTransaction */
        $anyTransaction = Argument::any();
        $transactionRepositoryProphecy
            ->create($anyTransaction)
            ->shouldBeCalledOnce();

        $authorizePaymentService = $this->prophesize(AuthorizePayment::class);
        $authorizePaymentService
            ->isAuthorized()
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $container->set(UserCanPayUseCase::class, $userCanPayProphecy->reveal());
        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $container->set(WalletRepository::class, $walletRepositoryProphecy->reveal());
        $container->set(TransactionRepository::class, $transactionRepositoryProphecy->reveal());
        $container->set(AuthorizePayment::class, $authorizePaymentService->reveal());

        $useCase     = $container->get(PayUserUseCase::class);
        $transaction = $useCase->handle((string) $payer->getDocumentNumber(), (string) $payee->getDocumentNumber(), $value);

        $this->assertEquals(TransactionStatusEnum::NOT_AUTHORIZED, $transaction->getStatus());
    }
}
