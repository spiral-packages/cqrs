<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Command;

use Spiral\Cqrs\CommandInterface;

class UpdateUser implements CommandInterface
{
    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {
    }
}
