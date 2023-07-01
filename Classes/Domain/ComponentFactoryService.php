<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ControllerContext;
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
     * @var array<string, ComponentFactory>
     */
    private readonly array $componentNamesAndFactories;

    public function has(ComponentName $name): bool
    {
        $this->initialize();
        return isset($this->componentNamesAndFactories[$name->value]);
    }

    public function render(ComponentName $name, Node $node, ControllerContext $controllerContext): string
    {
        $this->initialize();

        $factory = $this->componentNamesAndFactories[$name->value];
        $content = $factory->render($node, $controllerContext);

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

            if ($factory instanceof ComponentFactory) {
                // some higher oder function returned this
                $componentNamesAndFactories[$factory->name->value] = $factory;
                continue;
            }

            if (!$factory instanceof \Closure) {
                continue;
                throw new \RuntimeException(sprintf('Invalid component factory at: %s. A closure must be returned.', $includedFile));
            }


            $factory = ComponentFactory::fromClosure($factory);

            $componentNamesAndFactories[$factory->name->value] = $factory;
        }

        $this->componentNamesAndFactories = $componentNamesAndFactories;

    }

}
