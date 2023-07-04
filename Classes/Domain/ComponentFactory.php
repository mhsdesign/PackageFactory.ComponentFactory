<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use PackageFactory\ComponentFactory\Application\Component;
use PackageFactory\ComponentFactory\Application\RenderingStuff;

final readonly class ComponentFactory
{
    public function __construct(
        private \Closure $fn,
        public ComponentName $name
    ) {
    }

    public static function fromClosure(\Closure $closure): self
    {
        $reflected = new \ReflectionFunction($closure);

        $attributes = $reflected->getAttributes();

        $name = null;

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($attributeInstance instanceof Component) {
                $name = $attributeInstance->name;
            }
        }

        if (!$name instanceof ComponentName) {
            throw new \RuntimeException(sprintf('Invalid component factory. No component annotation found.'));
        }

        return new self($closure, $name);
    }

    /**
     * @param \Closure(mixed $output, RenderingStuff $renderingStuff): string|\Stringable $fn
     */
    public function compose(\Closure $fn): self
    {
        return new self(
            fn ($renderingStuff) => $fn(($this->fn)($renderingStuff), $renderingStuff),
            $this->name
        );
    }

    public function render(RenderingStuff $renderingStuff): string|\Stringable
    {
        return ($this->fn)($renderingStuff);
    }
}
