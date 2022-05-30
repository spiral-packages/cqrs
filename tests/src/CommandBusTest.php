<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests;

use Mockery as m;
use Spiral\Cqrs\CommandBus;
use Spiral\Cqrs\CommandInterface;
use Spiral\Cqrs\Exception\CommandNotRegisteredException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class CommandBusTest extends TestCase
{
    private CommandBus $bus;
    private m\LegacyMockInterface|MessageBusInterface|m\MockInterface $messageBus;
    private m\LegacyMockInterface|m\MockInterface|CommandInterface $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bus = new CommandBus(
            $this->messageBus = m::mock(MessageBusInterface::class)
        );
        $this->command = m::mock(CommandInterface::class);
    }

    public function testDispatch(): void
    {
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->command)
            ->andReturn(
                new Envelope(new \stdClass(), [
                    new HandledStamp('foo', 'bar'),
                ])
            );

        $this->assertSame('foo', $this->bus->dispatch($this->command));
    }

    public function testDispatchWithoutStamp(): void
    {
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->command)
            ->andReturn(new Envelope(new \stdClass(), []));

        $this->assertNull($this->bus->dispatch($this->command));
    }

    public function testNoHandlerForMessageException(): void
    {
        $this->expectException(CommandNotRegisteredException::class);
        $this->expectErrorMessage(
            \sprintf('The command <%s> hasn\'t a command handler associated', $this->command::class)
        );

        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->command)
            ->andThrow(new NoHandlerForMessageException());

        $this->bus->dispatch($this->command);
    }

    public function testHandlerFailedException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Something went wrong.');

        $envelope = new Envelope(new \stdClass(), []);

        $exception = new \Exception('Something went wrong.');
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->command)
            ->andThrow(new HandlerFailedException($envelope, [
                $exception
            ]));

        $this->bus->dispatch($this->command);
    }
}
