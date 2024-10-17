<?php

namespace App\Application\UseCases\Wallet;

use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Factories\WalletFactory;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use Psr\Log\LoggerInterface;

class CreateWalletUseCase
{
    public function __construct(
        private readonly UserRepository  $userRepository,
        private readonly WalletRepository $walletRepository,
        private readonly WalletFactory $walletFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws NegativeBalanceException
     * @throws UserNotFoundException
     */
    public function handle(string $userId): Wallet
    {
        if (! $this->userRepository->userExistsById($userId)) {
            $this->logger->info(sprintf('User with ID %s not found', $userId));
            throw new UserNotFoundException();
        }

        $wallet = $this->walletFactory->create(null, 0, $userId);

        $this->walletRepository->create($wallet);
        $this->logger->info(sprintf('Wallet with id %s created for user_id %s.', $wallet->getId(), $wallet->getUserId()), $wallet->jsonSerialize());

        return $wallet;
    }
}
