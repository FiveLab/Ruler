## Russia has become a terrorist state.

<div style="font-size: 2em; color: #d0d7de;">
    <span style="background-color: #54aeff">&nbsp;#StandWith</span><span style="background-color: #d4a72c">Ukraine&nbsp;</span>
</div>

Ruler
=====

The library for apply the string rule to any query builders (Doctrine ORM, Elastica, etc...).

For start use ruler, you should create the target factories for all supported targets for you.
After, you can create ruler and apply any rules:

```php
<?php

use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\Targets;
use FiveLab\Component\Ruler\Target\DoctrineOrmTarget;
use FiveLab\Component\Ruler\Target\ElasticaTarget;

$targets = new Targets(
    new DoctrineOrmTarget(),
    new ElasticaTarget() 
);

$ruler = new Ruler($targets);

// Apply rules
$qb = $entityManager->createQueryBuilder()
    ->from('Product', 'products')
    ->select('products');

$ruler->apply($qb, 'category.key in (:categories) and enabled = :enabled and price > :price', [
    'categories' => ['cat1', 'cat2'], 
    'enabled' => true,
    'price' => 100
]);
```

> Note: system auto-detect joins based on dot (`.`) for SQL targets and nested for document targets.
> If column contain dot (in ES as an example), you can escape dot via `\` (`money\.amount`).

Development
-----------

For easy development you can use the `Docker`.

```bash
$ docker build -t ruler .
$ docker run -it -v $(pwd):/code --name ruler ruler bash

```

After success run and attach to container you must install vendors:

```bash
$ composer update
```

Before create the PR or merge into develop, please run next commands for validate code:

```bash
$ ./bin/phpunit

$ ./bin/phpcs --config-set show_warnings 0
$ ./bin/phpcs --standard=vendor/escapestudios/symfony2-coding-standard/Symfony/ src/
$ ./bin/phpcs --standard=tests/phpcs-ruleset.xml tests/

```
