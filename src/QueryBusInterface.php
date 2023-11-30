<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

interface QueryBusInterface
{
    /**
     * @template TResult
     * @param QueryInterface<TResult> $query
     *
     * @return TResult
     */
    public function ask(QueryInterface $query): mixed;
}
