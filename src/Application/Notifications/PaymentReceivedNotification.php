<?php

declare(strict_types=1);

namespace App\Application\Notifications;

use App\Domain\Entities\Transaction;
use App\Domain\Entities\User;

interface PaymentReceivedNotification
{
    public function notify(User $user, Transaction $transaction): void;
}
