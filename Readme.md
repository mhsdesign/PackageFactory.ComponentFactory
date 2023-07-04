
## PackageFactory.ComponentFactory

**JUST A SUPER EARLY DRAFT** 

Are you ready for it?

```yaml
PackageFactory:
  ComponentFactory:
    autoInclude:
      'nodetypes://Neos.Demo/**/*.php': true
```

NodeTypes/Content/Headline/Headline.php
```php
<?php

use PackageFactory\ComponentFactory\Application\RenderingStuff;
use PackageFactory\ComponentFactory\Application\Component;

return #[Component('Neos.Demo:Content.Headline')] function(RenderingStuff $renderingStuff): string
{
    return $renderingStuff->node->getProperty('title');
};
```

Let the battle begin.

## Features

### Planned: The preferred way to create for the presentation components is to use the [ComponentEngine](https://github.com/PackageFactory/PackageFactory.ComponentEngine)

```php
<?php

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use PackageFactory\ComponentFactory\Application\Component;
use YourVendor\Site\Presentation\Headline;

return #[Component('Neos.Demo:Content.Headline')] function(RenderingStuff $renderingStuff): Headline
{
    return new Headline(
        content: $renderingStuff->node->getProperty('title')
    )
};
```

### Inline Editing

The annotation `#[Component('Neos.Demo:Content.Headline')]` behaves similar to the `Neos.Neos:ContentComponent`, as it will genrate the necessary markup to make the content editable.

For inline editable properties, you can use the helper function:

```php
use function PackageFactory\ComponentFactory\Application\editable;

$content = editable(
    renderingStuff: $renderingStuff,
    property: 'title',
    block: false
);
```

### Legacy: Use your Fusion Presentation Components

To ease the migration to the [ComponentEngine](https://github.com/PackageFactory/PackageFactory.ComponentEngine) you can leverage [PackageFactory.FusionFactory](https://github.com/mhsdesign/PackageFactory.FusionFactory) to reuse your existing Presentational components written in Fusion + AFX.

the equivalent of this fusion integration

```neosfusion
prototype(Neos.Demo:Content.Headline) < prototype(Neos.Neos:ContentComponent) {
    tagName = ${q(node).property('tagName')}
    tagStyle = ${q(node).property('tagStyle')}
    content = Neos.Neos:Editable {
        property = 'title'
        block = false
    }

    renderer = afx`<Neos.Demo:Presentation.Headline {...props} />`
}
```

would be:

```php
<?php

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use PackageFactory\ComponentFactory\Application\Component;

use function PackageFactory\ComponentFactory\Application\editable;
use function PackageFactory\FusionFactory\Application\fusionRenderer;
use function PackageFactory\FusionFactory\Application\component;

return fusionRenderer(#[Component('Neos.Demo:Content.Headline')] function(RenderingStuff $renderingStuff)
{
    $node = $renderingStuff->node;

    $tagName = $node->getProperty('tagName');
    $tagStyle = $node->getProperty('tagStyle');

    $content = editable(
        renderingStuff: $renderingStuff,
        property: 'title',
        block: false
    );

    return component(
        name: 'Neos.Demo:Presentation.Headline',
        props: compact('tagName', 'tagStyle', 'content')
    );
});
```

### Planned: Caching

### Planned: Out of band reload

