<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Exception;

use Symfony\Component\Messenger\Envelope;

class HandlerTypeIsNotSupported extends CqrsException
{
    public function __construct(Envelope $envelope, ?Throwable $previous = null)
    {
        parent::__construct(
            \sprintf(
                "Message handler [%s] is not supported",
                get_class($envelope->getMessage())
            ),
            previous: $previous
        );
    }
}
