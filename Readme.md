
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

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use PackageFactory\ComponentFactory\Domain\Component;

return #[Component('Neos.Demo:Content.Headline')] function(Node $node): string
{
    return $node->getProperty('title');
};
```

Let the battle begin.

## Features

### Planned: The preferred way to create for the presentation components is to use the [ComponentEngine](https://github.com/PackageFactory/PackageFactory.ComponentEngine)

```php
<?php

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use PackageFactory\ComponentFactory\Domain\Component;
use YourVendor\Site\Presentation\Headline;

return #[Component('Neos.Demo:Content.Headline')] function(Node $node): Headline
{
    return new Headline(
        content: $node->getProperty('title')
    )
};
```

### Inline Editing

The annotation `#[Component('Neos.Demo:Content.Headline')]` behaves similar to the `Neos.Neos:ContentComponent`, as it will genrate the necessary markup to make the content editable.

For inline editable properties, you can use the helper function:

```php
use function PackageFactory\ComponentFactory\Domain\editable;

$content = editable(
    node: $node,
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
use PackageFactory\ComponentFactory\Domain\Component;

use function PackageFactory\ComponentFactory\Domain\editable;
use function PackageFactory\FusionFactory\Domain\fusionRenderer;
use function PackageFactory\FusionFactory\Domain\component;

return fusionRenderer(#[Component('Neos.Demo:Content.Headline')] function(Node $node)
{
    $tagName = $node->getProperty('tagName');
    $tagStyle = $node->getProperty('tagStyle');

    $content = editable(
        node: $node,
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

