<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests;

use Mockery as m;
use Spiral\Cqrs\Exception\CommandNotRegisteredException;
use Spiral\Cqrs\Exception\QueryNotRegisteredException;
use Spiral\Cqrs\QueryBus;
use Spiral\Cqrs\QueryInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class QueryBusTest extends TestCase
{
    private QueryBus $bus;
    private m\LegacyMockInterface|MessageBusInterface|m\MockInterface $messageBus;
    private m\LegacyMockInterface|m\MockInterface|QueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bus = new QueryBus(
            $this->messageBus = m::mock(MessageBusInterface::class)
        );
        $this->query = m::mock(QueryInterface::class);
    }

    public function testAsk(): void
    {
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->query)
            ->andReturn(
                new Envelope(new \stdClass(), [
                    new HandledStamp('foo', 'bar'),
                ])
            );

        $this->assertSame('foo', $this->bus->ask($this->query));
    }

    public function testDispatchWithoutStamp(): void
    {
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->query)
            ->andReturn(new Envelope(new \stdClass(), []));

        $this->assertNull($this->bus->ask($this->query));
    }

    public function testNoHandlerForMessageException(): void
    {
        $this->expectException(QueryNotRegisteredException::class);
        $this->expectErrorMessage(
            \sprintf('The query <%s> hasn\'t a query handler associated', $this->query::class)
        );

        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->query)
            ->andThrow(new NoHandlerForMessageException());

        $this->bus->ask($this->query);
    }

    public function testHandlerFailedException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Something went wrong.');

        $envelope = new Envelope(new \stdClass(), []);

        $exception = new \Exception('Something went wrong.');
        $this->messageBus->shouldReceive('dispatch')
            ->once()
            ->with($this->query)
            ->andThrow(new HandlerFailedException($envelope, [
                $exception
            ]));

        $this->bus->ask($this->query);
    }
}
