# (CQRS) Command/Query bus implementation for Spiral Framework

[![PHP](https://img.shields.io/packagist/php-v/spiral-packages/cqrs.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/cqrs)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral-packages/cqrs.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/cqrs)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral-packages/cqrs.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/cqrs)
[![run-tests](https://github.com/spiral-packages/cqrs/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spiral-packages/cqrs/actions/workflows/run-tests.yml)

It's a lightweight messaging facade. It allows you to define the API of your model with the help of messages.

- Command messages describe actions your model can handle.
- Query messages describe available information that can be fetched from your (read) model.

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+
- Spiral framework 3.0+

## Installation

You can install the package via composer:

```bash
composer require spiral-packages/cqrs
```

After package install you need to register bootloader from the package.

```php
protected const LOAD = [
    // ...
    \Spiral\Cqrs\Bootloader\CqrsBootloader::class,
];
```

> Note: if you are using [`spiral-packages/discoverer`](https://github.com/spiral-packages/discoverer),
> you don't need to register bootloader by yourself.

## Usage

You can also register command and query handlers via attributes

### Commands

#### Command definition

```php
class StoreUser implements \Spiral\Cqrs\CommandInterface
{
    public function __construct(
        public Uuid $uuid,
        public string $username,
        public string $password,
        public \DateTimeImmutable $registeredAt,
    ) {
    }
}
```

#### Command handler definition

To register command handler you just need to add attribute on method that should be invoked.

```php
class StoreUserHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {

    }

    #[\Spiral\Cqrs\Attribute\CommandHandler]
    public function __invoke(StoreUser $command)
    {
        $this->entityManager->persist(
            new User(
                $command->uuid,
                $command->username,
                $command->password,
                $command->registeredAt
            )
        );

        $this->entityManager->run();
    }
}
```

#### Dispatch command

```php
use Ramsey\Uuid\Uuid;

class UserController
{
    public function store(UserStoreRequest $request, \Spiral\Cqrs\CommandBusInterface $bus)
    {
        $bus->dispatch(new StoreUser(
           $uuid = Uuid::uuid4(),
           $request->getUsername(),
           $request->getPassword(),
           new \DateTimeImmutable()
        ));

        return $uuid;
    }
}
```

### Queries

#### Query definition

```php
class FindAllUsers implements \Spiral\Cqrs\QueryInterface
{
    public function __construct(
        public array $roles = []
    ) {
    }
}
```

```php
class FindUserById implements \Spiral\Cqrs\QueryInterface
{
    public function __construct(
        public Uuid $uuid
    ) {
    }
}
```

#### Query handler definition

```php
class UsersQueries
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    #[\Spiral\Cqrs\Attribute\QueryHandler]
    public function findAll(FindAllUsers $query): UserCollection
    {
        $scope = [];
        if ($query->roles !== []) {
            $scope['roles'] = $query->roles
        }

        return new UserCollection(
            $this->users->findAll($scope)
        );
    }

    #[\Spiral\Cqrs\Attribute\QueryHandler]
    public function findById(FindUserById $query): UserResource
    {
        return new UserResource(
            $this->users->findByPK($query->uuid)
        );
    }
}
```

#### Dispatch queries

```php
use Ramsey\Uuid\Uuid;

class UserController
{
    public function index(UserFilters $filters, \Spiral\Cqrs\QueryBusInterface $bus)
    {
        return $bus->ack(
            new FindAllUsers($filters->roles())
        )->toArray();
    }

    public function show(string $uuid, \Spiral\Cqrs\QueryBusInterface $bus)
    {
        return $bus->ack(
            new FindUserById(Uuid::fromString($uuid))
        )->toArray();
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [butschster](https://github.com/spiral-packages)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
