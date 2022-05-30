<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Command;

use Spiral\Cqrs\CommandInterface;

class UpdateUser implements CommandInterface
{
    public function __construct(
        public string $username,
        public string $password
    ) {
    }
}
