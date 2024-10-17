<?php

namespace App\Application\Contracts;

interface AuthorizePayment
{
    public function isAuthorized(): bool;
}
