<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Command;

use Spiral\Cqrs\CommandInterface;

final class StoreUser implements CommandInterface
{
    public function __construct(
        public string $uuid,
        public string $username,
        public string $password
    ) {
    }
}
