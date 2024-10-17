<?php

namespace App\Application\Contracts;

interface UnitOfWork
{
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;
}
