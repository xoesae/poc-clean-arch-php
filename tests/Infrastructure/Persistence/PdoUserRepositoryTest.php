<?php

namespace Tests\Infrastructure\Persistence;

use App\Domain\Contracts\PasswordHasher;
use App\Domain\Contracts\UuidGenerator;
use App\Domain\Entities\User;
use App\Domain\Enums\UserTypeEnum;
use App\Infrastructure\Persistence\PdoUserRepository;
use DateTimeImmutable;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use PDO;
use Tests\TestCase;

class PdoUserRepositoryTest extends TestCase
{
    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     */
    public function testCreateUser()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $passwordHasher = $container->get(PasswordHasher::class);
        $uuidGenerator  = $container->get(UuidGenerator::class);
        $userRepository = $container->get(PdoUserRepository::class);
        $pdo            = $container->get(PDO::class);

        $stmt = $pdo->prepare('DELETE FROM users;');
        $stmt->execute();

        $user = new User(
            $uuidGenerator->generateAsString(),
            'Bill Gates',
            'bill@example.com',
            $passwordHasher->hash('password'),
            '620.758.220-93',
            UserTypeEnum::COMMON,
            new DateTimeImmutable()
        );

        $result = $userRepository->create($user);

        $this->assertTrue($result);
    }

    /**
     * @throws NotFoundException
     * @throws DependencyException
     * @throws Exception
     */
    public function testFindUserByDocumentNumber()
    {
        /** @var Container $container */
        $container = $this->getAppInstance()->getContainer();

        $passwordHasher = $container->get(PasswordHasher::class);
        $uuidGenerator  = $container->get(UuidGenerator::class);
        $userRepository = $container->get(PdoUserRepository::class);
        $pdo            = $container->get(PDO::class);

        $stmt = $pdo->prepare('DELETE FROM users;');
        $stmt->execute();

        $user = new User(
            $uuidGenerator->generateAsString(),
            'Bill Gates',
            'bill@example.com',
            $passwordHasher->hash('password'),
            '620.758.220-93',
            UserTypeEnum::COMMON,
            new DateTimeImmutable()
        );
        $userRepository->create($user);

        $fetched = $userRepository->findUserByDocumentNumber($user->getDocumentNumber());
        $this->assertEquals($fetched->getId(), $user->getId());
    }
}
