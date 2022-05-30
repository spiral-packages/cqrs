<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App\Query;

use Spiral\Cqrs\QueryInterface;

final class FindUserById implements QueryInterface
{
    public function __construct(
        public readonly int $id
    ) {
    }
}
