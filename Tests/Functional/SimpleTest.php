<?php

declare(strict_types=1);

namespace PackageFactory\ComponentFactory\Tests\Functional;

use Neos\ContentRepository\Core\Projection\ContentGraph\ContentSubgraphInterface;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Neos\Service\ContentElementWrappingService;
use PackageFactory\ComponentFactory\Application\RenderingStuff;
use PackageFactory\ComponentFactory\Domain\ComponentFactoryService;
use PackageFactory\ComponentFactory\Domain\ComponentName;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimpleTest extends FunctionalTestCase
{
    use NodeMockTrait;

    public function test(): void
    {
        $componentFactoryService = $this->objectManager->get(ComponentFactoryService::class);

        $name = ComponentName::fromString('PackageFactory.ComponentFactory:FactoryA');

        self::assertTrue($componentFactoryService->has($name));

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $fakeWrapping = $this->getMockBuilder(ContentElementWrappingService::class)->disableOriginalConstructor()->getMock();
        $fakeWrapping->expects(self::once())->method('wrapContentObject')->willReturnArgument(1);
        $container->expects(self::once())->method('get')->with(ContentElementWrappingService::class)->willReturn(
            $fakeWrapping
        );

        $rendered = $componentFactoryService->render($name, new RenderingStuff(
            node: $this->createNodeMock(properties: ['title' => 'Hello World']),
            documentNode: $this->createNodeMock(),
            siteNode: $this->createNodeMock(),
            inBackend: false,
            subgraph: $this->createStub(ContentSubgraphInterface::class),
            request: $this->createStub(ServerRequestInterface::class),
            di: $container,
        ));

        self::assertSame(
            'FactoryA: Hello World',
            $rendered
        );
    }
}
