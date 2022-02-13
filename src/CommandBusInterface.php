<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}
