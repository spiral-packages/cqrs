<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Exception;

use Spiral\Cqrs\QueryInterface;

class QueryNotRegisteredException extends CqrsException
{
    public function __construct(QueryInterface $query, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf("The query <%s> hasn't a query handler associated", get_class($query)),
            (int)$previous->getCode(),
            $previous
        );
    }
}
