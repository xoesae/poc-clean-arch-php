<?php

declare(strict_types=1);

namespace App\Domain\Persistence;

use App\Domain\Entities\User;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\ValueObjects\DocumentNumber;

interface UserRepository
{
    /**
     * @param DocumentNumber $documentNumber
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserByDocumentNumber(DocumentNumber $documentNumber): User;

    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool;

    public function userExistsById(string $id): bool;

    public function userExistsByDocumentNumber(DocumentNumber $documentNumber): bool;
}
