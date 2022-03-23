<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Bootloader;

use Spiral\Attributes\AttributeReader;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Cqrs\CommandBus;
use Spiral\Cqrs\CommandBusInterface;
use Spiral\Cqrs\HandlersLocator;
use Spiral\Cqrs\QueryBus;
use Spiral\Cqrs\QueryBusInterface;
use Spiral\Tokenizer\ClassesInterface;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class CqrsBootloader extends Bootloader
{
    protected const SINGLETONS = [
        HandlersLocatorInterface::class => [self::class, 'initHandlersLocator'],
        MessageBusInterface::class => [self::class, 'initMessageBus'],
        CommandBusInterface::class => CommandBus::class,
        QueryBusInterface::class => QueryBus::class,
    ];

    public function initMessageBus(HandlersLocatorInterface $locator): MessageBusInterface
    {
        return new MessageBus([
            new HandleMessageMiddleware(
                $locator
            ),
        ]);
    }

    public function initHandlersLocator(
        Container $container,
        ClassesInterface $classes
    ): HandlersLocatorInterface {
        return new HandlersLocator(
            $container,
            $classes,
            new AttributeReader()
        );
    }
}
