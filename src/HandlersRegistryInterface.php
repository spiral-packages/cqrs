<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

/**
 * @psalm-type THandler = array{0: class-string, 1:non-empty-string}
 */
interface HandlersRegistryInterface
{
    /**
     * @param class-string<CommandInterface> $command
     * @param THandler $handler
     */
    public function registerCommandHandler(string $command, array $handler): void;

    /**
     * @param class-string<QueryInterface> $query
     * @param THandler $handler
     */
    public function registerQueryHandler(string $query, array $handler): void;
}
