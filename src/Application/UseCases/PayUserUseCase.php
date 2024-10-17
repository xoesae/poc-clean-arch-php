<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Contracts\AuthorizePayment;
use App\Application\Contracts\UnitOfWork;
use App\Application\UseCases\User\UserCanPayUseCase;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Transaction;
use App\Domain\Enums\TransactionStatusEnum;
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
use App\Domain\ValueObjects\DocumentNumber;
use Psr\Log\LoggerInterface;

readonly class PayUserUseCase
{
    public function __construct(
        private UserRepository $userRepository,
        private WalletRepository $walletRepository,
        private TransactionRepository $transactionRepository,
        private UserCanPayUseCase $userCanPayUseCase,
        private AuthorizePayment $authorizePayment,
        private UnitOfWork $unitOfWork,
        private UuidGenerator $uuidGenerator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param string $fromDocumentNumber
     * @param string $toDocumentNumber
     * @param int $value
     * @return Transaction
     * @throws InsufficientBalanceException
     * @throws InvalidPayeeWalletException
     * @throws NotValidTransactionValueException
     * @throws PaymentNotAuthorizedException
     * @throws ShopkeeperCannotPayException
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     * @throws NegativeBalanceException
     */
    public function handle(string $fromDocumentNumber, string $toDocumentNumber, int $value): Transaction
    {
        $this->userCanPayUseCase->handle($fromDocumentNumber, $value);

        $payer      = $this->userRepository->findUserByDocumentNumber(new DocumentNumber($fromDocumentNumber));
        $fromWallet = $this->walletRepository->findWalletByUserId($payer->getId());

        $payee    = $this->userRepository->findUserByDocumentNumber(new DocumentNumber($toDocumentNumber));
        $toWallet = $this->walletRepository->findWalletByUserId($payee->getId());

        $transaction = new Transaction(
            $this->uuidGenerator->generateAsString(),
            $fromWallet->getId(),
            $toWallet->getId(),
            $value,
            TransactionStatusEnum::PENDING,
        );

        if (! $this->authorizePayment->isAuthorized()) {
            $this->logger->info(
                sprintf(
                    'Transaction %s not authorized for user %s',
                    $transaction->getId(),
                    $payer->getId(),
                ),
                $transaction->jsonSerialize()
            );
            $transaction->setStatus(TransactionStatusEnum::NOT_AUTHORIZED);
        }

        $this->unitOfWork->beginTransaction();

        if ($transaction->isPending()) {
            $fromWallet->pay($value);
            $toWallet->receive($value);
            $this->walletRepository->update($fromWallet->getId(), $fromWallet);
            $this->walletRepository->update($toWallet->getId(), $toWallet);
            $transaction->setStatus(TransactionStatusEnum::COMPLETED);
        }

        $this->transactionRepository->create($transaction);

        $this->unitOfWork->commit();

        $this->logger->info(
            sprintf(
                'Transaction %s created',
                $transaction->getId(),
            ),
            $transaction->jsonSerialize()
        );

        if ($transaction->isNotAuthorized()) {
            throw new PaymentNotAuthorizedException();
        }

        return $transaction;
    }
}
