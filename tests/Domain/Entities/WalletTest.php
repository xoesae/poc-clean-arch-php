<?php

namespace Domain\Entities;

use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\InsufficientBalanceException;
use App\Domain\Exceptions\NegativeBalanceException;
use DateTimeImmutable;
use Tests\TestCase;

class WalletTest extends TestCase
{
    public function walletProvider(): array
    {
        $uuidGenerator = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        return [
            [$uuidGenerator->generateAsString(), 0, $uuidGenerator->generateAsString(), new DateTimeImmutable()],
            [$uuidGenerator->generateAsString(), 1000, $uuidGenerator->generateAsString(), new DateTimeImmutable()],
            [$uuidGenerator->generateAsString(), 50000, $uuidGenerator->generateAsString(), new DateTimeImmutable()],
        ];
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @throws NegativeBalanceException
     */
    public function testGetters(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $wallet = new Wallet($id, $balance, $userId, $createdAt);

        $this->assertEquals($id, $wallet->getId());
        $this->assertEquals($balance, $wallet->getBalance());
        $this->assertEquals($userId, $wallet->getUserId());
        $this->assertEquals($createdAt->format('Y-m-d H:i:s'), $wallet->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @return void
     * @throws NegativeBalanceException
     */
    public function testInvalidBalance(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $this->expectException(NegativeBalanceException::class);

        new Wallet($id, -100, $userId, $createdAt);
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @return void
     * @throws NegativeBalanceException
     */
    public function testPayWithInsufficientBalance(
        string $id,
        int $balance,
        string $userId,
        DateTimeImmutable $createdAt
    ) {
        $this->expectException(InsufficientBalanceException::class);

        $wallet = new Wallet($id, $balance, $userId, $createdAt);
        $wallet->pay($balance + 1);
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @return void
     * @throws NegativeBalanceException
     */
    public function testPayWithNegativeValue(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $this->expectException(NegativeBalanceException::class);

        $wallet = new Wallet($id, $balance, $userId, $createdAt);
        $wallet->pay(-1000);
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @return void
     * @throws NegativeBalanceException
     */
    public function testPayWithAllBalance(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $wallet = new Wallet($id, $balance, $userId, $createdAt);
        $wallet->pay($balance);

        $this->assertEquals(0, $wallet->getBalance());
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @return void
     * @throws NegativeBalanceException
     */
    public function testReceiveNegativeValue(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $this->expectException(NegativeBalanceException::class);

        $wallet = new Wallet($id, $balance, $userId, $createdAt);
        $wallet->receive(-1000);
    }

    /**
     * @dataProvider walletProvider
     * @param string $id
     * @param int $balance
     * @param string $userId
     * @param DateTimeImmutable $createdAt
     * @throws NegativeBalanceException
     */
    public function testJsonSerialize(string $id, int $balance, string $userId, DateTimeImmutable $createdAt)
    {
        $wallet = new Wallet($id, $balance, $userId, $createdAt);

        $expectedPayload = json_encode([
            'id'         => $id,
            'balance'    => $balance,
            'user_id'    => $userId,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertEquals($expectedPayload, json_encode($wallet));
    }
}
