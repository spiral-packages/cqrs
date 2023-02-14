<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Core\Container;
use Spiral\Cqrs\CommandBus;
use Spiral\Cqrs\CommandBusInterface;
use Spiral\Cqrs\CqrsAttributesListener;
use Spiral\Cqrs\HandlersLocator;
use Spiral\Cqrs\HandlersRegistryInterface;
use Spiral\Cqrs\QueryBus;
use Spiral\Cqrs\QueryBusInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class CqrsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class,
    ];

    protected const SINGLETONS = [
        HandlersLocator::class => [self::class, 'initHandlersLocator'],
        HandlersLocatorInterface::class => HandlersLocator::class,
        HandlersRegistryInterface::class => HandlersLocator::class,
        MessageBusInterface::class => [self::class, 'initMessageBus'],
        CommandBusInterface::class => CommandBus::class,
        QueryBusInterface::class => QueryBus::class,
    ];

    public function init(
        TokenizerListenerBootloader $tokenizer,
        CqrsAttributesListener $listener
    ): void {
        $tokenizer->addListener($listener);
    }

    public function initMessageBus(HandlersLocatorInterface $locator): MessageBusInterface
    {
        return new MessageBus([
            new HandleMessageMiddleware(
                $locator
            ),
        ]);
    }

    public function initHandlersLocator(
        Container $container
    ): HandlersLocatorInterface {
        return new HandlersLocator(
            $container,
        );
    }
}
