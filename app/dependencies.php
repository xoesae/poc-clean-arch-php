<?php

declare(strict_types=1);

use App\Application\Contracts\AuthorizePayment as AuthorizePaymentInterface;
use App\Application\Contracts\UnitOfWork;
use App\Application\Settings\SettingsInterface;
use App\Application\UseCases\PayUserUseCase;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\UserCanPayUseCase;
use App\Domain\Contracts\PasswordHasher as PasswordHasherInterface;
use App\Domain\Contracts\UuidGenerator;
use App\Infrastructure\Services\AuthorizePayment;
use App\Infrastructure\Services\PasswordHasher;
use App\Infrastructure\Services\PdoUnitOfWork;
use App\Infrastructure\Services\Uuid7Generator;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PDO::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $databaseSettings = $settings->get('database');

            $host = $databaseSettings['connection']['host'];
            $db = $databaseSettings['connection']['dbname'];
            $user = $databaseSettings['connection']['user'];
            $password = $databaseSettings['connection']['password'];
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $user, $password, $options);
        },

        // Contracts
        PasswordHasherInterface::class => autowire(PasswordHasher::class),
        UuidGenerator::class => autowire(Uuid7Generator::class),
        AuthorizePaymentInterface::class => autowire(AuthorizePayment::class),
        UnitOfWork::class => autowire(PdoUnitOfWork::class),

        // Use Cases
        CreateUserUseCase::class => autowire(CreateUserUseCase::class),
        UserCanPayUseCase::class => autowire(UserCanPayUseCase::class),
        PayUserUseCase::class => autowire(PayUserUseCase::class),
    ]);
};
