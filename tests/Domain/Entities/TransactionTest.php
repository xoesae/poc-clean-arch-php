<?php

namespace Domain\Entities;

use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\Transaction;
use App\Domain\Enums\TransactionStatusEnum;
use App\Domain\Exceptions\InvalidPayeeWalletException;
use App\Domain\Exceptions\NotValidTransactionValueException;
use DateTimeImmutable;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function transactionProvider(): array
    {
        $uuidGenerator = $this->getAppInstance()->getContainer()->get(UuidGenerator::class);

        return [
            [
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                1000,
                TransactionStatusEnum::PENDING,
                new DateTimeImmutable()
            ],
            [
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                15000,
                TransactionStatusEnum::PENDING,
                new DateTimeImmutable()
            ],
            [
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                $uuidGenerator->generateAsString(),
                100000,
                TransactionStatusEnum::PENDING,
                new DateTimeImmutable()
            ],
        ];
    }

    /**
     * @dataProvider transactionProvider
     * @param string $id
     * @param string $payerWalletId
     * @param string $payeeWalletId
     * @param int $value
     * @param TransactionStatusEnum $status
     * @param DateTimeImmutable|null $createdAt
     * @throws NotValidTransactionValueException|InvalidPayeeWalletException
     */
    public function testGetters(
        string $id,
        string $payerWalletId,
        string $payeeWalletId,
        int $value,
        TransactionStatusEnum $status,
        ?DateTimeImmutable $createdAt
    ) {
        $transaction = new Transaction($id, $payerWalletId, $payeeWalletId, $value, $status, $createdAt);

        $this->assertEquals($id, $transaction->getId());
        $this->assertEquals($payerWalletId, $transaction->getPayerWalletId());
        $this->assertEquals($payeeWalletId, $transaction->getPayeeWalletId());
        $this->assertEquals($value, $transaction->getValue());
        $this->assertEquals($status, $transaction->getStatus());
        $this->assertEquals(
            $createdAt->format('Y-m-d H:i:s'),
            $transaction->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @dataProvider transactionProvider
     * @param string $id
     * @param string $payerWalletId
     * @param string $payeeWalletId
     * @param int $value
     * @param TransactionStatusEnum $status
     * @param DateTimeImmutable|null $createdAt
     * @throws NotValidTransactionValueException|InvalidPayeeWalletException
     */
    public function testTransactionWithZeroValue(
        string $id,
        string $payerWalletId,
        string $payeeWalletId,
        int $value,
        TransactionStatusEnum $status,
        ?DateTimeImmutable $createdAt
    ) {
        $this->expectException(NotValidTransactionValueException::class);

        new Transaction($id, $payerWalletId, $payeeWalletId, 0, $status, $createdAt);
    }

    /**
     * @dataProvider transactionProvider
     * @param string $id
     * @param string $payerWalletId
     * @param string $payeeWalletId
     * @param int $value
     * @param TransactionStatusEnum $status
     * @param DateTimeImmutable|null $createdAt
     * @throws NotValidTransactionValueException|InvalidPayeeWalletException
     */
    public function testTransactionWithNegativeValue(
        string $id,
        string $payerWalletId,
        string $payeeWalletId,
        int $value,
        TransactionStatusEnum $status,
        ?DateTimeImmutable $createdAt
    ) {
        $this->expectException(NotValidTransactionValueException::class);

        new Transaction($id, $payerWalletId, $payeeWalletId, - $value, $status, $createdAt);
    }

    /**
     * @dataProvider transactionProvider
     * @param string $id
     * @param string $payerWalletId
     * @param string $payeeWalletId
     * @param int $value
     * @param TransactionStatusEnum $status
     * @param DateTimeImmutable|null $createdAt
     * @throws NotValidTransactionValueException|InvalidPayeeWalletException
     */
    public function testTransactionForYourself(
        string $id,
        string $payerWalletId,
        string $payeeWalletId,
        int $value,
        TransactionStatusEnum $status,
        ?DateTimeImmutable $createdAt
    ) {
        $this->expectException(InvalidPayeeWalletException::class);

        new Transaction($id, $payerWalletId, $payerWalletId, $value, $status, $createdAt);
    }

    /**
     * @dataProvider transactionProvider
     * @param string $id
     * @param string $payerWalletId
     * @param string $payeeWalletId
     * @param int $value
     * @param TransactionStatusEnum $status
     * @param DateTimeImmutable|null $createdAt
     * @throws NotValidTransactionValueException|InvalidPayeeWalletException
     */
    public function testJsonSerialize(
        string $id,
        string $payerWalletId,
        string $payeeWalletId,
        int $value,
        TransactionStatusEnum $status,
        ?DateTimeImmutable $createdAt
    ) {
        $transaction = new Transaction($id, $payerWalletId, $payeeWalletId, $value, $status, $createdAt);

        $expectedPayload = json_encode([
            'id'            => $id,
            'payerWalletId' => $payerWalletId,
            'payeeWalletId' => $payeeWalletId,
            'value'         => $value,
            'status'        => $status->value,
            'createdAt'     => $createdAt->format('Y-m-d H:i:s'),
        ]);

        $this->assertEquals($expectedPayload, json_encode($transaction));
    }
}
