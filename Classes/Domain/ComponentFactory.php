<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Mvc\Controller\ControllerContext;

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

    public function wrap(\Closure $fn): self
    {
        return new self(
            fn (...$args) => $fn(($this->fn)(...$args), ...$args),
            $this->name
        );
    }

    public function render(Node $node, ControllerContext $controllerContext): string|\Stringable
    {
        return ($this->fn)($node, $controllerContext);
    }
}
