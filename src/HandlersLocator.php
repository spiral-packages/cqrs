<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Cqrs\Attribute\CommandHandler;
use Spiral\Cqrs\Attribute\QueryHandler;
use Spiral\Cqrs\Exception\HandlerTypeIsNotSupported;
use Spiral\Cqrs\Exception\InvalidHandlerException;
use Spiral\Tokenizer\ClassesInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class HandlersLocator implements HandlersLocatorInterface
{
    private array $commandHandlers = [];
    private array $queryHandlers = [];
    private bool $precessed = false;

    public function __construct(
        private Container $container,
        private ClassesInterface $classes,
        private ReaderInterface $reader,
    ) {
    }

    public function getHandlers(Envelope $envelope): iterable
    {
        if (! $this->precessed) {
            $this->lookForHandlers();
        }

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
                if (in_array($name, $seen)) {
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
        $class = get_class($envelope->getMessage());

        return [$class => $class]
            + class_parents($class)
            + class_implements($class)
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

        return $received->getTransportName() === $expectedTransport;
    }

    /**
     * @param array{0: class-string, 1: non-empty-string}  $handler
     */
    private function buildHandlerDescriptor(array $handler): HandlerDescriptor
    {
        return new HandlerDescriptor([
            $this->container->make($handler[0]),
            $handler[1],
        ]);
    }

    private function lookForHandlers()
    {
        foreach ($this->classes->getClasses() as $class) {
            foreach ($class->getMethods() as $method) {
                if ($this->reader->firstFunctionMetadata($method, CommandHandler::class)) {
                    $this->processCommandHandler($method);
                }

                if ($this->reader->firstFunctionMetadata($method, QueryHandler::class)) {
                    $this->processQueryHandler($method);
                }
            }
        }
    }

    private function processCommandHandler(\ReflectionMethod $method): void
    {
        $this->assertHandlerMethodIsPublic($method);

        foreach ($method->getParameters() as $parameter) {
            if (is_a($parameter->getType()->getName(), CommandInterface::class, true)) {
                $this->commandHandlers[$parameter->getType()->getName()][] = [
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ];
            }
        }
    }

    private function processQueryHandler(\ReflectionMethod $method)
    {
        $this->assertHandlerMethodIsPublic($method);

        foreach ($method->getParameters() as $parameter) {
            if (is_a($parameter->getType()->getName(), QueryInterface::class, true)) {
                $this->queryHandlers[$parameter->getType()->getName()][] = [
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ];
            }
        }
    }

    /**
     * @param \ReflectionMethod $method
     * @return void
     * @throws InvalidHandlerException
     */
    private function assertHandlerMethodIsPublic(\ReflectionMethod $method): void
    {
        if (! $method->isPublic()) {
            throw new InvalidHandlerException(
                \sprintf(
                    'Handler method %s:%s should be public.',
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                )
            );
        }
    }
}
