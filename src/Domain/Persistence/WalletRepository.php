<?php

namespace App\Domain\Persistence;

use App\Domain\Entities\Wallet;
use App\Domain\Exceptions\WalletNotFoundException;

interface WalletRepository
{
    /**
     * @param string $userId
     * @return Wallet
     * @throws WalletNotFoundException
     */
    public function findWalletByUserId(string $userId): Wallet;

    public function create(Wallet $wallet): bool;

    public function update(string $id, Wallet $wallet): bool;
}
