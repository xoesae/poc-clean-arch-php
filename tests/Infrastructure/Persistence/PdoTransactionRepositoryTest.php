<?php

namespace Tests\Infrastructure\Persistence;

use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Transaction;
use App\Domain\Enums\TransactionStatusEnum;
use App\Infrastructure\Persistence\PdoTransactionRepository;
use DateTimeImmutable;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use PDO;
use Tests\TestCase;

class PdoTransactionRepositoryTest extends TestCase
{
    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     */
    public function testCreateTransaction()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $uuidGenerator         = $container->get(UuidGenerator::class);
        $transactionRepository = $container->get(PdoTransactionRepository::class);
        $pdo                   = $container->get(PDO::class);

        $stmt = $pdo->prepare('DELETE FROM transactions;');
        $stmt->execute();

        $transaction = new Transaction(
            $uuidGenerator->generateAsString(),
            $uuidGenerator->generateAsString(),
            $uuidGenerator->generateAsString(),
            100,
            TransactionStatusEnum::PENDING,
            new DateTimeImmutable()
        );
        $result = $transactionRepository->create($transaction);

        $stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ?;');
        $stmt->execute([$transaction->getId()]);

        $this->assertTrue($result);
        $this->assertCount(1, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
