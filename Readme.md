
## PackageFactory.ComponentFactory

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
