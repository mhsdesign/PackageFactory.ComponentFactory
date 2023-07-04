<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Application;

use Neos\ContentRepository\Core\Projection\ContentGraph\Filter\FindChildNodesFilter;
use Neos\ContentRepository\Core\SharedModel\Node\NodeName;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Neos\FrontendRouting\NodeAddressFactory;
use Neos\Neos\FrontendRouting\NodeUriBuilder;
use Neos\Neos\Fusion\Helper\NodeHelper;
use Neos\Neos\Service\ContentElementEditableService;
use Neos\Neos\Service\ContentElementWrappingService;
use PackageFactory\ComponentFactory\Domain\ComponentFactoryService;
use PackageFactory\ComponentFactory\Domain\ComponentName;
use Psr\Http\Message\UriInterface;

function editable(RenderingStuff $renderingStuff, string $property, bool $block = true): string
{
    $content = ($block ? '<div>' : '')
        . ($renderingStuff->node->getProperty($property) ?: '')
        . ($block ? '</div>' : '');

    if (!$renderingStuff->inBackend) {
        return $content;
    }

    $contentElementEditableService = $renderingStuff->di->get(ContentElementEditableService::class);
    return $contentElementEditableService->wrapContentProperty(
        $renderingStuff->node,
        $property,
        $content
    );
}

function content(RenderingStuff $renderingStuff): string
{
    $componentFactoryService = $renderingStuff->di->get(ComponentFactoryService::class);
    return $componentFactoryService->render(
        ComponentName::fromString($renderingStuff->node->nodeTypeName->value),
        $renderingStuff
    );
}

function contentCollection(RenderingStuff $renderingStuff, string|NodeName|null $nodeName): string
{
    if (is_string($nodeName)) {
        $nodeName = NodeName::fromString($nodeName);
    }

    $contentCollectionNode = $renderingStuff->di->get(NodeHelper::class)->nearestContentCollection($renderingStuff->node, $nodeName?->value ?? '');

    $childNodes = $renderingStuff->subgraph->findChildNodes(
        $contentCollectionNode->nodeAggregateId, FindChildNodesFilter::create()
    );

    $contents = [];

    $componentFactoryService = $renderingStuff->di->get(ComponentFactoryService::class);

    foreach ($childNodes as $childNode) {
        if (!$componentFactoryService->has(ComponentName::fromString($childNode->nodeTypeName->value))) {
            // @todo provide fallback by rendering through fusion? No?
            $contents[] = sprintf('<br><strong style="color: darkred">Warning:</strong> "%s" cant be rendered via component factory.<br>', $childNode->nodeTypeName->value);
            continue;
        }

        $contents[] = content($renderingStuff->withNode($childNode));
    }

    $content = '<div class="neos-contentcollection"'
        . ($renderingStuff->inBackend ? ' data-__neos-insertion-anchor' : '')
        . '>'
        . join('', $contents)
        . '</div>';

    return $renderingStuff->di->get(ContentElementWrappingService::class)->wrapContentObject($contentCollectionNode, $content, '' /* @todo */);
}

function getNodeUri(RenderingStuff $renderingStuff, bool $absolute = false, ?string $format = null): UriInterface
{
    $nodeAddressFactory = NodeAddressFactory::create($renderingStuff->contentRepository);
    $nodeAddress = $nodeAddressFactory->createFromNode($renderingStuff->node);
    $uriBuilder = new UriBuilder();
    $uriBuilder->setRequest(
        ActionRequest::fromHttpRequest($renderingStuff->request)
    );
    $uriBuilder
        ->setCreateAbsoluteUri($absolute)
        ->setFormat($format ?: 'html');

    return NodeUriBuilder::fromUriBuilder($uriBuilder)->uriFor($nodeAddress);
}
