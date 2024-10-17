<?php

declare(strict_types=1);

use App\Domain\Persistence\TransactionRepository;
use App\Domain\Persistence\UserRepository;
use App\Domain\Persistence\WalletRepository;
use App\Infrastructure\Persistence\PdoTransactionRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use App\Infrastructure\Persistence\PdoWalletRepository;
use DI\ContainerBuilder;

use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => autowire(PdoUserRepository::class),
        WalletRepository::class => autowire(PdoWalletRepository::class),
        TransactionRepository::class => autowire(PdoTransactionRepository::class),
    ]);
};
