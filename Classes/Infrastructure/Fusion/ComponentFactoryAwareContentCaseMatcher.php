<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Infrastructure\Fusion;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\FusionObjects\CaseImplementation;
use Neos\Fusion\FusionObjects\MatcherImplementation;
use PackageFactory\ComponentFactory\Application\RenderingStuff;
use PackageFactory\ComponentFactory\Domain\ComponentFactoryService;
use PackageFactory\ComponentFactory\Domain\ComponentName;

/**
 * Fusion connector to ComponentFactory (see override fusion)
 *
 * And replacement for the @see MatcherImplementation
 * Which checks if "type" has a component factory and uses that instead
 */
class ComponentFactoryAwareContentCaseMatcher extends AbstractFusionObject
{
    #[Flow\Inject]
    protected ComponentFactoryService $componentFactoryService;

    #[Flow\Inject]
    protected ObjectManager $objectManager;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    /**
     * If $condition matches, render $type and return it. Else, return MATCH_NORESULT.
     *
     * @return mixed
     */
    public function evaluate()
    {
        if (!$this->fusionValue('condition')) {
            return CaseImplementation::MATCH_NORESULT;
        }
        return $this->evaluateRenderer();
    }

    private function evaluateRenderer(): mixed
    {
        $canRenderWithRenderer = $this->runtime->canRender($this->path . '/renderer');
        if ($canRenderWithRenderer) {
            return $this->fusionValue('renderer');
        }

        $renderPath = $this->fusionValue('renderPath');
        if ($renderPath !== null) {
            if (str_starts_with($renderPath, '/')) {
                // absolute path
                return $this->runtime->render(substr($renderPath, 1));
            }
            // relative path
            return $this->runtime->render(
                $this->path . '/' . str_replace('.', '/', $renderPath)
            );
        }

        //
        // THIS IS WHERE THE MAGIC HAPPENS!
        //
        $name = ComponentName::fromString($this->fusionValue('type'));
        if ($this->fusionValue('type') && $this->componentFactoryService->has($name)) {
            $context = $this->runtime->getCurrentContext();

            $inBackend = match($action = $this->runtime->getControllerContext()->getRequest()->getControllerActionName()) {
                'show' => false,
                'preview' => true,
                default => throw new \InvalidArgumentException('unknown action ' . $action)
            };

            /** @var Node $node */
            $node = $context['node'];

            $cr = $this->contentRepositoryRegistry->get($node->subgraphIdentity->contentRepositoryId);

            return $this->componentFactoryService->render(
                $name,
                new RenderingStuff(
                    $context['node'],
                    $context['documentNode'],
                    $context['site'],
                    $inBackend,
                    $cr,
                    $this->contentRepositoryRegistry->subgraphForNode($node),
                    $this->runtime->getControllerContext()->getRequest()->getHttpRequest(),
                    $this->objectManager
                )
            );
        }

        return $this->runtime->render(
            $this->path . '/element<' . $this->fusionValue('type') . '>'
        );
    }
}
