<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Tests\App;

interface EntityManagerInterface
{
    public function store(array $data): void;
}
