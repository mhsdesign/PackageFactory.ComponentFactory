<?php

use PackageFactory\ComponentFactory\Application\Component;
use PackageFactory\ComponentFactory\Application\RenderingStuff;

return #[Component('PackageFactory.ComponentFactory:FactoryA')] function (RenderingStuff $renderingStuff): string
{
    return 'FactoryA: ' . $renderingStuff->node->getProperty('title');
};
