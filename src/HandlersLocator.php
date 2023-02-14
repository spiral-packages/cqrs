<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

use Spiral\Core\Container;
use Spiral\Cqrs\Exception\HandlerTypeIsNotSupported;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class HandlersLocator implements HandlersLocatorInterface, HandlersRegistryInterface
{
    private array $commandHandlers = [];
    private array $queryHandlers = [];

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function getHandlers(Envelope $envelope): iterable
    {
        $seen = [];

        $handlers = match (true) {
            $envelope->getMessage() instanceof QueryInterface => $this->queryHandlers,
            $envelope->getMessage() instanceof CommandInterface => $this->commandHandlers,
            default => throw new HandlerTypeIsNotSupported($envelope)
        };

        foreach (self::listTypes($envelope) as $type) {
            foreach ($handlers[$type] ?? [] as $handler) {
                $handlerDescriptor = $this->buildHandlerDescriptor($handler);

                if (! $this->shouldHandle($envelope, $handlerDescriptor)) {
                    continue;
                }

                $name = $handlerDescriptor->getName();
                if (\in_array($name, $seen)) {
                    continue;
                }

                $seen[] = $name;

                yield $handlerDescriptor;
            }
        }
    }

    /** @internal */
    public static function listTypes(Envelope $envelope): array
    {
        $class = \get_class($envelope->getMessage());

        return [$class => $class]
            + \class_parents($class)
            + \class_implements($class)
            + ['*' => '*'];
    }

    private function shouldHandle(Envelope $envelope, HandlerDescriptor $handlerDescriptor): bool
    {
        if (null === $received = $envelope->last(ReceivedStamp::class)) {
            return true;
        }

        if (null === $expectedTransport = $handlerDescriptor->getOption('from_transport')) {
            return true;
        }

        /** @var ReceivedStamp $received */
        return $received->getTransportName() === $expectedTransport;
    }

    /**
     * @param array{0: class-string, 1: non-empty-string} $handler
     */
    private function buildHandlerDescriptor(array $handler): HandlerDescriptor
    {
        return new HandlerDescriptor([
            $this->container->make($handler[0]),
            $handler[1],
        ]);
    }

    public function registerCommandHandler(string $command, array $handler): void
    {
        $this->commandHandlers[$command][] = $handler;
    }

    public function registerQueryHandler(string $query, array $handler): void
    {
        $this->queryHandlers[$query][] = $handler;
    }
}
