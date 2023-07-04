<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Application;

use PackageFactory\ComponentFactory\Domain\ComponentName;

#[\Attribute]
final readonly class Component
{
    public ComponentName $name;

    public function __construct(
        string $name
    ) {
        $this->name = ComponentName::fromString($name);
    }
}
