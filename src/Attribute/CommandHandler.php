<?php

declare(strict_types=1);

namespace Spiral\Cqrs\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class CommandHandler
{
}
