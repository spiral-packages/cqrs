<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

interface CommandBusInterface
{
    /**
     * @template TResult
     * @param CommandInterface<TResult> $command
     *
     * @return TResult
     */
    public function dispatch(CommandInterface $command): mixed;
}
