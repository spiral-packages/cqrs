<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests;

use Spiral\Cqrs\Tests\App\Command\StoreUser;
use Spiral\Cqrs\Tests\App\EntityManagerInterface;
use Spiral\Cqrs\Tests\App\Query\FindUserById;
use Spiral\Cqrs\Tests\App\UserRepositoryInterface;

final class HandlersLocatorTest extends TestCase
{
    public function testHandleCommand(): void
    {
        $em = $this->mockContainer(EntityManagerInterface::class);

        $em->shouldReceive('store')->once()->with([
            'uuid' => 'uuid-string',
            'username' => 'john_smith',
            'password' => 'secret',
        ]);

        $this->getContainer()
            ->get(\Spiral\Cqrs\CommandBusInterface::class)->dispatch(
                new StoreUser(
                    'uuid-string',
                    'john_smith',
                    'secret'
                )
            );
    }

    public function testHandleQuery(): void
    {
        $em = $this->mockContainer(UserRepositoryInterface::class);

        $em->shouldReceive('findByPk')->once()->with(123)->andReturn(
            $user = [
                'uuid' => 'uuid-string',
                'username' => 'john_smith',
                'password' => 'secret',
            ]
        );

        $this->assertSame(
            $user,
            $this->getContainer()->get(\Spiral\Cqrs\QueryBusInterface::class)->ask(new FindUserById(123))
        );
    }
}
