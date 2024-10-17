<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use App\Domain\Persistence\UserRepository as UserRepositoryInterface;
use App\Domain\ValueObjects\DocumentNumber;
use DateTimeImmutable;
use PDO;

readonly class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findUserByDocumentNumber(DocumentNumber $documentNumber): User
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE document_number = ?;');
        $stmt->execute([$documentNumber]);
        /** @var array{
         * id: string,
         * name: string,
         * email: string,
         * password: string,
         * document_number: string,
         * type: string,
         * created_at: string,
         * } $user */
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return new User($user['id'], $user['name'], $user['email'], $user['password'], $user['document_number'], UserTypeEnum::from($user['type']), new DateTimeImmutable($user['created_at']));
    }

    public function create(User $user): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO users VALUES (:id, :name, :email, :password, :document_number, :type, :created_at);');
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':document_number', $user->getDocumentNumber()->getValue());
        $stmt->bindValue(':type', $user->getType()->value);
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function userExistsById(string $id): bool
    {
        $stmt = $this->connection->prepare('SELECT 1 FROM users WHERE id = ? LIMIT 1;');
        $stmt->execute([$id]);

        return (bool) $stmt->fetch();
    }

    public function userExistsByDocumentNumber(DocumentNumber $documentNumber): bool
    {
        $stmt = $this->connection->prepare('SELECT 1 FROM users WHERE document_number = ? LIMIT 1;');
        $stmt->execute([(string) $documentNumber]);

        return (bool) $stmt->fetch();
    }
}
