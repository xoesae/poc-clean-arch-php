<?php

namespace App\Application\UseCases\User;

use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Exceptions\ShopkeeperCannotPayException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Exceptions\WalletNotFoundException;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use App\Domain\ValueObjects\DocumentNumber;
use Psr\Log\LoggerInterface;

class UserCanPayUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly WalletRepository $walletRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ShopkeeperCannotPayException
     * @throws InsufficientBalanceException
     * @throws UserNotFoundException
     * @throws WalletNotFoundException
     */
    public function handle(string $documentNumber, int $value): void
    {
        $payer = $this->userRepository->findUserByDocumentNumber(new DocumentNumber($documentNumber));

        if ($payer->isShopkeeper()) {
            $this->logger->info(
                sprintf('Payer %s cannot pay because he is shopkeeper', $payer->getId()),
                $payer->jsonSerialize()
            );
            throw new ShopkeeperCannotPayException();
        }

        $fromWallet = $this->walletRepository->findWalletByUserId($payer->getId());

        if ($fromWallet->getBalance() < $value) {
            $this->logger->info(
                sprintf(
                    'Payer %s cannot pay $%s because has $%s balance.',
                    $payer->getId(),
                    number_format($value, 2, ',', '.'),
                    number_format($fromWallet->getBalance(), 2, ',', '.'),
                ),
                $fromWallet->jsonSerialize()
            );
            throw new InsufficientBalanceException();
        }
    }
}
