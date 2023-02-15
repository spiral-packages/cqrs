<?php

declare(strict_types=1);

namespace Spiral\Cqrs;

use ReflectionMethod;
use Spiral\Attributes\ReaderInterface;
use Spiral\Cqrs\Attribute\CommandHandler;
use Spiral\Cqrs\Attribute\QueryHandler;
use Spiral\Cqrs\Exception\InvalidHandlerException;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;

/**
 * @psalm-suppress InvalidAttribute
 * @psalm-suppress UndefinedAttributeClass
 */
#[TargetAttribute(class: CommandHandler::class)]
#[TargetAttribute(class: QueryHandler::class)]
final class CqrsAttributesListener implements TokenizationListenerInterface
{
    private array $commandHandlers = [];
    private array $queryHandlers = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly HandlersRegistryInterface $registry,
    ) {
    }

    public function listen(\ReflectionClass $class): void
    {
        foreach ($class->getMethods() as $method) {
            if ($this->reader->firstFunctionMetadata($method, CommandHandler::class)) {
                $this->assertHandlerMethodIsPublic($method);
                $this->processCommandHandler($method);
            }

            if ($this->reader->firstFunctionMetadata($method, QueryHandler::class)) {
                $this->assertHandlerMethodIsPublic($method);
                $this->processQueryHandler($method);
            }
        }
    }

    public function finalize(): void
    {
        foreach ($this->commandHandlers as $command => $handlers) {
            foreach ($handlers as $handler) {
                $this->registry->registerCommandHandler($command, $handler);
            }
        }

        foreach ($this->queryHandlers as $command => $handlers) {
            foreach ($handlers as $handler) {
                $this->registry->registerQueryHandler($command, $handler);
            }
        }
    }

    private function processCommandHandler(ReflectionMethod $method): void
    {
        $this->assertHandlerMethodIsPublic($method);

        foreach ($this->getMethodParameters($method) as $parameter) {
            if (\is_a($parameter->getName(), CommandInterface::class, true)) {
                $this->commandHandlers[$parameter->getName()][] = [
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ];
            }
        }
    }

    private function processQueryHandler(ReflectionMethod $method)
    {
        $this->assertHandlerMethodIsPublic($method);

        foreach ($this->getMethodParameters($method) as $parameter) {
            if (\is_a($parameter->getName(), QueryInterface::class, true)) {
                $this->queryHandlers[$parameter->getName()][] = [
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ];
            }
        }
    }

    /**
     * @throws InvalidHandlerException
     */
    private function assertHandlerMethodIsPublic(ReflectionMethod $method): void
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

    /**
     * @return \Generator<int, \ReflectionNamedType|\ReflectionType|null>
     */
    private function getMethodParameters(ReflectionMethod $method): \Generator
    {
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getType() instanceof \ReflectionUnionType) {
                foreach ($parameter->getType()->getTypes() as $type) {
                    yield $type;
                }
            } else {
                yield $parameter->getType();
            }
        }
    }
}
