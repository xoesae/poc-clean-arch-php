<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Domain\Persistence\WalletRepository;
use DateTimeImmutable;
use PDO;

readonly class PdoWalletRepository implements WalletRepository
{
    public function __construct(
        private Pdo $connection
    ) {
    }

    /**
     * @inheritDoc
     * @throws NegativeBalanceException
     */
    public function findWalletByUserId(string $userId): Wallet
    {
        $stmt = $this->connection->prepare('SELECT * FROM wallets WHERE user_id = ?;');
        $stmt->execute([$userId]);
        /** @var array{
         * id: string,
         * balance: int,
         * user_id: string,
         * created_at: string,
         * } $wallet */
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Wallet($wallet['id'], $wallet['balance'], $wallet['user_id'], new DateTimeImmutable($wallet['created_at']));
    }

    public function create(Wallet $wallet): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO wallets VALUES (:id, :balance, :user_id, :created_at);');
        $stmt->bindValue(':id', $wallet->getId());
        $stmt->bindValue(':balance', $wallet->getBalance());
        $stmt->bindValue(':user_id', $wallet->getUserId());
        $stmt->bindValue(':created_at', $wallet->getCreatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function update(string $id, Wallet $wallet): bool
    {
        $stmt = $this->connection->prepare('UPDATE wallets SET balance = :balance, user_id = :user_id, created_at = :created_at WHERE id = :id;');
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':balance', $wallet->getBalance());
        $stmt->bindValue(':user_id', $wallet->getUserId());
        $stmt->bindValue(':created_at', $wallet->getCreatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }
}
