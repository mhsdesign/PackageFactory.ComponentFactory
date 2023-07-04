<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Application;

use Neos\ContentRepository\Core\ContentRepository;
use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RenderingStuff
{
    public function __construct(
        public Node $node,
        public Node $documentNode,
        public Node $siteNode,
        public bool $inBackend,
        public ContentSubgraphInterface $subgraph,
        public ServerRequestInterface $request,
        public ContainerInterface $di
    ) {
        // assert($this->documentNode->nodeType->isOfType('Neos.Neos:Document'), sprintf('Expected $documentNode to be of type "Neos.Neos:Document". Got type: "%s".', $this->siteNode->nodeTypeName->value));
        // assert($this->subgraph->findParentNode($this->siteNode->nodeAggregateId)->nodeType->isOfType('Neos.Neos:Sites'), sprintf('Expected $siteNode to be childNode of node of type "Neos.Neos:Sites".'));
    }

    public function withNode(Node $node): self
    {
        return new self(
            $node,
            $this->documentNode,
            $this->siteNode,
            $this->inBackend,
            $this->subgraph,
            $this->request,
            $this->di
        );
    }

    public function getContentRepository(): ContentRepository
    {
        return $this->di->get(ContentRepositoryRegistry::class)->get($this->node->subgraphIdentity->contentRepositoryId);
    }

    // todo withDocument?
}
