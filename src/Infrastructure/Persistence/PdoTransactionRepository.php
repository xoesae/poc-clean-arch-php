<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Transaction;
use App\Domain\Persistence\TransactionRepository;
use PDO;

class PdoTransactionRepository implements TransactionRepository
{
    public function __construct(
        private readonly Pdo $connection
    ) {
    }

    public function create(Transaction $transaction): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO transactions VALUES (:id, :payer_id, :payee_id, :value, :status, :created_at);');
        $stmt->bindValue(':id', $transaction->getId());
        $stmt->bindValue(':payer_id', $transaction->getPayerWalletId());
        $stmt->bindValue(':payee_id', $transaction->getPayeeWalletId());
        $stmt->bindValue(':value', $transaction->getValue());
        $stmt->bindValue(':status', $transaction->getStatus()->value);
        $stmt->bindValue(':created_at', $transaction->getCreatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }
}
