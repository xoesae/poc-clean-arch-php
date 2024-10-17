<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\UnitOfWork;
use PDO;

class PdoUnitOfWork implements UnitOfWork
{
    public function __construct(
        private readonly PDO $connection,
    ) {
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollBack();
    }
}
