<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

use Spiral\Cqrs\Exception\QueryNotRegisteredException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class QueryBus implements QueryBusInterface
{
    use MessageBusExceptionTrait;

    public function __construct(
        private readonly MessageBusInterface $bus
    ) {
    }

    /**
     * @throws QueryNotRegisteredException
     * @throws \Throwable
     *
     * @psalm-suppress InvalidReturnType
     */
    public function ask(QueryInterface $query): mixed
    {
        try {
            $envelope = $this->bus->dispatch($query);

            /** @var HandledStamp $stamp */
            $stamp = $envelope->last(HandledStamp::class);

            return $stamp?->getResult();
        } catch (NoHandlerForMessageException $e) {
            throw new QueryNotRegisteredException($query, $e);
        } catch (HandlerFailedException $e) {
            $this->throwException($e);
        }
    }
}
