<?php

namespace App\Domain\Enums;

enum TransactionStatusEnum: string
{
    case PENDING        = 'PENDING';
    case NOT_AUTHORIZED = 'NOT_AUTHORIZED';
    case CANCELLED      = 'CANCELLED';
    case REFUNDED       = 'REFUNDED';
    case COMPLETED      = 'COMPLETED';
}
