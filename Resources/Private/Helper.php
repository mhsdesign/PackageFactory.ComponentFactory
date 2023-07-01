<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Neos\Fusion\Helper\NodeHelper;
use Neos\Neos\Service\ContentElementEditableService;
use Neos\Neos\Service\ContentElementWrappingService;

function editable(Node $node, string $property, bool $block = true): string
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

function content(Node $node, ControllerContext $controllerContext): string
{
    $objectManager = Bootstrap::$staticObjectManager;

    $componentFactoryService = $objectManager->get(ComponentFactoryService::class);
    return $componentFactoryService->render(
        ComponentName::fromString($node->nodeTypeName->value),
        $node,
        $controllerContext
    );
}

function contentCollection(Node $node, string|NodeName|null $nodeName, ControllerContext $controllerContext): string
{
    if (is_string($nodeName)) {
        $nodeName = NodeName::fromString($nodeName);
    }

    $objectManager = Bootstrap::$staticObjectManager;

    $contentCollectionNode = $objectManager->get(NodeHelper::class)->nearestContentCollection($node, $nodeName?->value ?? '');

    $childNodes = $objectManager->get(ContentRepositoryRegistry::class)->subgraphForNode($node)->findChildNodes(
        $contentCollectionNode->nodeAggregateId, FindChildNodesFilter::create()
    );

    $contents = [];

    $componentFactoryService = $objectManager->get(ComponentFactoryService::class);

    foreach ($childNodes as $childNode) {
        if (!$componentFactoryService->has(ComponentName::fromString($childNode->nodeTypeName->value))) {
            // @todo provide fallback by rendering through fusion? No?
            $contents[] = sprintf('<br><strong style="color: darkred">Warning:</strong> "%s" cant be rendered via component factory.<br>', $childNode->nodeTypeName->value);
            continue;
        }

        $contents[] = content($childNode, $controllerContext);
    }

    $inBackend = $objectManager->get(NodeHelper::class)->inBackend($node);

    $content = '<div class="neos-contentcollection"'
        . ($inBackend ? ' data-__neos-insertion-anchor' : '')
        . '>'
        . join('', $contents)
        . '</div>';

    return $objectManager->get(ContentElementWrappingService::class)->wrapContentObject($contentCollectionNode, $content, '' /* @todo */);
}
