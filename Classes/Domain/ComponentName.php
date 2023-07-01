<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

final readonly class ComponentName
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }
}
