<?php

namespace Domain\ValueObjects;

use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\DocumentNumberIsNotValidException;
use App\Domain\ValueObjects\DocumentNumber;
use Tests\TestCase;

class DocumentNumberTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function validShopkeeperDocumentNumberProvider(): array
    {
        return [['05.503.537/0001-98'], ['63.299.137/0001-09']];
    }

    /**
     * @return string[][]
     */
    public function validCommonDocumentNumberProvider(): array
    {
        return [['620.758.220-93'], ['658.358.790-40']];
    }

    /**
     * @return string[][]
     */
    public function invalidShopkeeperDocumentNumberProvider(): array
    {
        return [['05.503.537/0001-70'], ['63.299.137/0001-0911']];
    }

    /**
     * @return string[][]
     */
    public function invalidCommonDocumentNumberProvider(): array
    {
        return [['620.758.220-70'], ['658.358.790-4'], ['658.358.790-444']];
    }

    /**
     * @dataProvider validCommonDocumentNumberProvider
     * @param string $value
     * @return void
     */
    public function testCommonDocumentNumberIsValid(string $value)
    {
        $document = new DocumentNumber($value, UserTypeEnum::COMMON);

        $this->assertEquals(preg_replace('/\D/', '', $value), (string) $document);
    }

    /**
     * @dataProvider validShopkeeperDocumentNumberProvider
     * @param string $value
     * @return void
     */
    public function testShopkeeperDocumentNumberIsValid(string $value)
    {
        $document = new DocumentNumber($value, UserTypeEnum::SHOPKEEPER);

        $this->assertEquals(preg_replace('/\D/', '', $value), (string) $document);
    }

    /**
     * @dataProvider invalidCommonDocumentNumberProvider
     * @param string $value
     * @return void
     */
    public function testCommonDocumentNumberIsInvalid(string $value)
    {
        $this->expectException(DocumentNumberIsNotValidException::class);

        new DocumentNumber($value, UserTypeEnum::COMMON);
    }

    /**
     * @dataProvider invalidShopkeeperDocumentNumberProvider
     * @param string $value
     * @return void
     */
    public function testShopkeeperDocumentNumberIsInvalid(string $value)
    {
        $this->expectException(DocumentNumberIsNotValidException::class);

        new DocumentNumber($value, UserTypeEnum::SHOPKEEPER);
    }
}
