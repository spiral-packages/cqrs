<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Handler;

use Spiral\Cqrs\Tests\App\Command\StoreUser;
use Spiral\Cqrs\Tests\App\Command\UpdateUser;
use Spiral\Cqrs\Tests\App\EntityManagerInterface;

final class StoreUserHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {

    }

    #[\Spiral\Cqrs\Attribute\CommandHandler]
    public function __invoke(StoreUser|UpdateUser $command)
    {
        $this->entityManager->store([
            'uuid' => $command->uuid,
            'username' => $command->username,
            'password' => $command->password
        ]);
    }
}
