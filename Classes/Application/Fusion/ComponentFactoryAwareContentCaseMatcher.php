<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Application\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\FusionObjects\CaseImplementation;
use PackageFactory\ComponentFactory\Domain\ComponentFactoryService;
use PackageFactory\ComponentFactory\Domain\ComponentName;

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

        if ($this->fusionValue('type')
            && $this->componentFactoryService->has($name = ComponentName::fromString($this->fusionValue('type')))) {

           return $this->componentFactoryService->render($name, $this->runtime->getCurrentContext()['node']);
        }

        return $this->runtime->render(
            $this->path . '/element<' . $this->fusionValue('type') . '>'
        );
    }
}
