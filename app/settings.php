<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => getenv('APP_DEBUG'), // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => $_ENV['APP_NAME'],
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'database' => [
                    'connection' => [
                        'driver' => 'pdo_mysql',
                        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                        'port' => $_ENV['DB_PORT'] ?? 3306,
                        'dbname' => $_ENV['DB_NAME'],
                        'user' => $_ENV['DB_USER'],
                        'password' => $_ENV['DB_PASSWORD'],
                        'charset' => 'utf-8'
                    ],
                ],
                'payments' => [
                    'authorization' => [
                          'url' => 'https://util.devi.tools/api/v2/',
                    ],
                    'notifications' => [
                        'url' => 'https://util.devi.tools/api/v1/',
                    ]
                ],
            ]);
        }
    ]);
};
