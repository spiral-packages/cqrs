<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): mixed;
}
