<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

use Spiral\Cqrs\Exception\CommandNotRegisteredException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class CommandBus implements CommandBusInterface
{
    use MessageBusExceptionTrait;

    public function __construct(
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws CommandNotRegisteredException
     * @throws \Throwable
     *
     * @psalm-suppress InvalidReturnType
     */
    public function dispatch(CommandInterface $command): mixed
    {
        try {
            $envelope = $this->bus->dispatch($command);

            /** @var HandledStamp $stamp */
            $stamp = $envelope->last(HandledStamp::class);

            return $stamp?->getResult();
        } catch (NoHandlerForMessageException $e) {
            throw new CommandNotRegisteredException($command, $e);
        } catch (HandlerFailedException $e) {
            $this->throwException($e);
        }
    }
}
