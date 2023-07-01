<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Core\Bootstrap;
use Neos\Neos\Service\ContentElementEditableService;

function editable(Node $node, string $property, bool $block = true)
{
    $contentElementEditableService = Bootstrap::$staticObjectManager->get(ContentElementEditableService::class);
    return $contentElementEditableService->wrapContentProperty(
        $node,
        $property,
        ($block ? '<div>' : '')
        . ($node->getProperty($property) ?: '')
        . ($block ? '</div>' : '')
    );
}
