<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\Bootloader;

use Spiral\Cqrs\CommandBus;
use Spiral\Cqrs\CommandBusInterface;
use Spiral\Cqrs\HandlersLocator;
use Spiral\Cqrs\QueryBus;
use Spiral\Cqrs\QueryBusInterface;
use Spiral\Cqrs\Tests\TestCase;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

final class CqrsBootloaderTest extends TestCase
{
    public function testCommandBusInterface()
    {
        $this->assertContainerBoundAsSingleton(
            CommandBusInterface::class,
            CommandBus::class
        );
    }

    public function testQueryBusInterface()
    {
        $this->assertContainerBoundAsSingleton(
            QueryBusInterface::class,
            QueryBus::class
        );
    }

    public function testMessageBusInterface()
    {
        $this->assertContainerBoundAsSingleton(
            MessageBusInterface::class,
            MessageBus::class
        );
    }

    public function testHandlersLocatorInterface()
    {
        $this->assertContainerBoundAsSingleton(
            HandlersLocatorInterface::class,
            HandlersLocator::class
        );
    }
}
