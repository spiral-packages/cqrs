<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App;

interface UserRepositoryInterface
{
    public function findByPk(int $pk): array;
}
