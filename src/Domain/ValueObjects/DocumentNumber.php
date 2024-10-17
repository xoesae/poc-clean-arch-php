<?php

namespace App\Domain\ValueObjects;

use App\Domain\Enums\UserTypeEnum;
use App\Domain\Exceptions\DocumentNumberIsNotValidException;

class DocumentNumber
{
    private string $value;

    public function __construct(string $value, ?UserTypeEnum $type = null)
    {
        $this->value = $this->validate($value, $type);
    }

    /**
     * @param string $value
     * @param UserTypeEnum|null $type
     * @return string
     */
    private function validate(string $value, ?UserTypeEnum $type = null): string
    {
        $value = preg_replace('/\D/', '', $value) ?? '';

        if (strlen($value) !== 11 && strlen($value) !== 14) {
            throw new DocumentNumberIsNotValidException();
        }

        if (($type === UserTypeEnum::COMMON || strlen($value) === 11) && !$this->validateCommonDocumentNumber($value)) {
            throw new DocumentNumberIsNotValidException();
        }

        if (($type === UserTypeEnum::SHOPKEEPER || strlen($value) === 14) && !$this->validateShopkeeperDocumentNumber($value)) {
            throw new DocumentNumberIsNotValidException();
        }

        return $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validateCommonDocumentNumber(string $value): bool
    {
        if (strlen($value) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $value)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += intval($value[$c]) * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($value[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validateShopkeeperDocumentNumber(string $value): bool
    {
        if (strlen($value) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $value)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += intval($value[$i]) * $m;
                $m = ($m == 2 ? 9 : --$m);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($value[$i] != $d) {
                return false;
            }
        }

        return true;
    }
}
