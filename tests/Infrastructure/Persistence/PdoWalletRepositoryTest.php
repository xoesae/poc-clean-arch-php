<?php

namespace Tests\Infrastructure\Persistence;

use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\NegativeBalanceException;
use App\Infrastructure\Persistence\PdoWalletRepository;
use DateTimeImmutable;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use PDO;
use Tests\TestCase;

class PdoWalletRepositoryTest extends TestCase
{
    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     * @throws NegativeBalanceException
     */
    public function testCreateWallet()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $uuidGenerator    = $container->get(UuidGenerator::class);
        $walletRepository = $container->get(PdoWalletRepository::class);
        $pdo              = $container->get(PDO::class);

        $stmt = $pdo->prepare('DELETE FROM wallets;');
        $stmt->execute();

        $wallet = new Wallet(
            $uuidGenerator->generateAsString(),
            0,
            $uuidGenerator->generateAsString(),
            new DateTimeImmutable()
        );
        $result = $walletRepository->create($wallet);

        $stmt = $pdo->prepare('SELECT * FROM wallets WHERE id = ?;');
        $stmt->execute([$wallet->getId()]);

        $this->assertTrue($result);
        $this->assertCount(1, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     * @throws NegativeBalanceException
     */
    public function testUpdateWallet()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $uuidGenerator    = $container->get(UuidGenerator::class);
        $walletRepository = $container->get(PdoWalletRepository::class);

        $wallet = new Wallet(
            $uuidGenerator->generateAsString(),
            0,
            $uuidGenerator->generateAsString(),
            new DateTimeImmutable()
        );
        $walletRepository->create($wallet);

        $this->assertTrue($walletRepository->update($wallet->getId(), $wallet));
    }

    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     * @throws NegativeBalanceException
     */
    public function testFindWalletByUserId()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $uuidGenerator    = $container->get(UuidGenerator::class);
        $walletRepository = $container->get(PdoWalletRepository::class);

        $wallet = new Wallet(
            $uuidGenerator->generateAsString(),
            0,
            $uuidGenerator->generateAsString(),
            new DateTimeImmutable()
        );

        $walletRepository->create($wallet);

        $fetched = $walletRepository->findWalletByUserId($wallet->getUserId());
        $this->assertEquals($fetched->getId(), $wallet->getId());
    }
}
