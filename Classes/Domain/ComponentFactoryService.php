<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\Core\ObjectTreeParser\FilePatternResolver;
use Neos\Neos\Service\ContentElementWrappingService;

#[Flow\Scope('singleton')]
class ComponentFactoryService
{
    #[Flow\InjectConfiguration(path: 'autoInclude', package: 'PackageFactory.ComponentFactory')]
    protected array $autoIncludeConfiguration = [];

    #[Flow\Inject]
    protected ContentElementWrappingService $contentElementWrappingService;

    /**
     * @var array<string, \Closure(): string|\Stringable>
     */
    private readonly array $componentNamesAndFactories;

    public function has(ComponentName $name): bool
    {
        $this->initialize();
        return isset($this->componentNamesAndFactories[$name->value]);
    }

    public function render(ComponentName $name, Node $node): string
    {
        $this->initialize();

        $factory = $this->componentNamesAndFactories[$name->value];
        $content = ($factory)($node);

        return $this->contentElementWrappingService->wrapContentObject($node, $content, '' /* @todo */);
    }


    private function initialize(): void
    {
        if (isset($this->componentNamesAndFactories)) {
            return;
        }

        $includedFiles = [];
        foreach ($this->autoIncludeConfiguration as $pattern => $enabled) {
            if (!$enabled) {
                continue;
            }
            $includedFiles = [...$includedFiles, ...FilePatternResolver::resolveFilesByPattern(
                filePattern: $pattern,
                filePathForRelativeResolves: null,
                defaultFileEndForUnspecificGlobbing: '.php'
            )];
        }
        $includedFiles = array_unique($includedFiles);

        $componentNamesAndFactories = [];

        $requireFile = \Closure::bind(static function ($file) {
            return require $file;
        }, null, null);


        foreach ($includedFiles as $includedFile) {
            // what if the dir is psr autoloaded? Better not
            $factory = $requireFile($includedFile);


            if (!$factory instanceof \Closure) {
                continue;
                throw new \RuntimeException(sprintf('Invalid component factory at: %s. A closure must be returned.', $includedFile));
            }

            $reflected = new \ReflectionFunction($factory);

            $attributes = $reflected->getAttributes();

            $name = null;

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                if ($attributeInstance instanceof Component) {
                    $name = $attributeInstance->name;
                }
            }

            if (!$name instanceof ComponentName) {
                throw new \RuntimeException(sprintf('Invalid component factory at: %s. No component annotation found.', $includedFile));
            }

            $componentNamesAndFactories[$name->value] = $factory;
        }

        $this->componentNamesAndFactories = $componentNamesAndFactories;

    }

}
