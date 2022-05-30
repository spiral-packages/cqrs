<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Handler;

use Spiral\Cqrs\Tests\App\Query\FindUserById;
use Spiral\Cqrs\Tests\App\UserRepositoryInterface;

final class UsersQueries
{
    public function __construct(
        private readonly UserRepositoryInterface $users
    ) {
    }

    #[\Spiral\Cqrs\Attribute\QueryHandler]
    public function findById(FindUserById $query): array
    {
        return $this->users->findByPK($query->id);
    }
}
