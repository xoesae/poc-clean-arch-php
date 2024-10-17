<?php

namespace App\Application\UseCases\User;

use App\Application\UseCases\Wallet\CreateWalletUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\DocumentNumberAlreadyInUseException;
use App\Domain\Exceptions\InvalidUserTypeException;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Factories\UserFactory;
use App\Domain\Persistence\UserRepository;
use App\Domain\ValueObjects\DocumentNumber;
use Psr\Log\LoggerInterface;

class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CreateWalletUseCase $createWalletUseCase,
        private readonly UserFactory $userFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws UserNotFoundException
     * @throws NegativeBalanceException
     * @throws DocumentNumberAlreadyInUseException
     */
    public function handle(string $name, string $email, string $password, string $documentNumber, string $type): User
    {
        if (! in_array($type, array_column(UserTypeEnum::cases(), 'value'))) {
            $this->logger->info(sprintf('Cannot create a user with type %s.', $type));
            throw new InvalidUserTypeException();
        }

        $documentNumber = new DocumentNumber($documentNumber);

        if ($this->userRepository->userExistsByDocumentNumber(new DocumentNumber($documentNumber))) {
            $this->logger->info(sprintf('User with document number %s already exists.', $documentNumber));
            throw new DocumentNumberAlreadyInUseException();
        }

        $user = $this->userFactory->create(null, $name, $email, $password, $documentNumber, $type);

        $this->userRepository->create($user);
        $this->logger->info(sprintf('User created with id %s', $user->getId()), $user->jsonSerialize());

        $this->createWalletUseCase->handle($user->getId());

        return $user;
    }
}
