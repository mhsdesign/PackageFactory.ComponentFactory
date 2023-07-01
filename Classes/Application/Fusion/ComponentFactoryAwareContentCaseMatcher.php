<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Application\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\FusionObjects\CaseImplementation;
use Neos\Fusion\FusionObjects\MatcherImplementation;
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
        if ($this->fusionValue('type')
            && $this->componentFactoryService->has($name = ComponentName::fromString($this->fusionValue('type')))) {

           return $this->componentFactoryService->render($name, $this->runtime->getCurrentContext()['node'], $this->runtime->getControllerContext());
        }

        return $this->runtime->render(
            $this->path . '/element<' . $this->fusionValue('type') . '>'
        );
    }
}
