
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
