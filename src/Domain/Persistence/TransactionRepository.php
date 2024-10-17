<?php

namespace App\Domain\Persistence;

use App\Domain\Entities\Transaction;

interface TransactionRepository
{
    public function create(Transaction $transaction): bool;
}
