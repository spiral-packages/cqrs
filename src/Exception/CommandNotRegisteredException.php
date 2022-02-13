<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Exception;

use Spiral\Cqrs\CommandInterface;

class CommandNotRegisteredException extends CqrsException
{
    public function __construct(CommandInterface $command, \Throwable $previous)
    {
        parent::__construct(
            sprintf("The command <%s> hasn't a command handler associated", get_class($command)),
            $previous->getCode(),
            $previous
        );
    }
}
