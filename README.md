## Russia has become a terrorist state.

<div style="font-size: 2em; color: #d0d7de;">
    <span style="background-color: #54aeff">&nbsp;#StandWith</span><span style="background-color: #d4a72c">Ukraine&nbsp;</span>
</div>

Ruler
=====

[![Build Status](https://github.com/FiveLab/Ruler/workflows/Testing/badge.svg?branch=master)](https://github.com/FiveLab/Ruler/actions)
[![Latest Stable Version](https://poser.pugx.org/fivelab/ruler/v)](https://packagist.org/packages/fivelab/ruler)
[![Total Downloads](https://poser.pugx.org/fivelab/ruler/downloads)](https://packagist.org/packages/fivelab/ruler)
[![PHP Version Require](https://poser.pugx.org/fivelab/ruler/require/php)](https://packagist.org/packages/fivelab/ruler)
[![License](https://poser.pugx.org/fivelab/ruler/license)](https://packagist.org/packages/fivelab/ruler)

Write a filter as a single string rule and apply it to different query builders — **Doctrine ORM**,
**Elasticsearch / OpenSearch** (via Elastica or the native clients) and **ClickHouse** — without
rewriting the condition for each backend.

```php
$ruler->apply($queryBuilder, 'category.key in (:categories) and price > :price', [
    'categories' => ['cat1', 'cat2'],
    'price'      => 100,
]);
```

The same rule string produces a DQL `WHERE` for Doctrine, a `bool` query for Elasticsearch, or a
`WHERE` clause for ClickHouse — you only change the target you pass in.

Why Ruler?
----------

* **One rule, many backends.** Reuse a filter across your database and your search index.
* **Safe values.** Rule values are always bound as query parameters, never concatenated into the query.
* **Composable.** Build rules from reusable specifications (`and` / `or`, per-target overrides).
* **Extensible.** Add your own operators or targets through small interfaces.
* **Maintained.** PHP 8.2+, Doctrine ORM 2 & 3, actively developed — a drop-in idea for the
  unmaintained [`kphoen/rulerz`](https://github.com/K-Phoen/rulerz).

Installation
------------

```bash
composer require fivelab/ruler
```

Install the packages for the targets you use (they are declared as `suggest`):

```bash
composer require doctrine/orm      # for the Doctrine ORM target
composer require ruflin/elastica   # for the Elasticsearch / OpenSearch target
```

Usage
-----

Create a `Ruler` for the target(s) you need, then `apply()` a rule to a query object.

### Doctrine ORM

```php
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\DoctrineOrmTarget;

$ruler = new Ruler(new DoctrineOrmTarget());

$qb = $entityManager->createQueryBuilder()
    ->select('products')
    ->from(Product::class, 'products');

$ruler->apply($qb, 'category.key in (:categories) and price > :price', [
    'categories' => ['cat1', 'cat2'],
    'price'      => 100,
]);

// The query builder now has the WHERE condition, the parameters and the joins applied.
$products = $qb->getQuery()->getResult();
```

Joins are detected automatically from the dotted path: `category.key` adds a `LEFT JOIN` on the
`category` association and filters on its `key` field. Nested associations (`variants.category.key`)
are supported too.

### Elasticsearch / OpenSearch

For the [`ruflin/elastica`](https://github.com/ruflin/Elastica) client, pass an `Elastica\Query`:

```php
use Elastica\Query;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\ElasticaTarget;

$ruler = new Ruler(new ElasticaTarget());

$query = new Query();
$query->setSize(20);

$ruler->apply($query, 'price > :price and tag = :tag', [
    'price' => 100,
    'tag'   => 'sale',
]);

$results = $index->search($query);
```

For the native `elasticsearch/elasticsearch` or `opensearch-project/opensearch-php` clients, use
`RawSearchQuery` and read the built body:

```php
use FiveLab\Component\Ruler\Query\RawSearchQuery;

$query = new RawSearchQuery();
$ruler->apply($query, 'price > :price', ['price' => 100]);

$response = $client->search([
    'index' => 'products',
    'body'  => $query->toArray(),
]);
```

> `apply()` only sets the `query` part, so `size`, `sort`, `aggs`, etc. are preserved. Calling it
> several times combines the conditions with `bool.must`.

### ClickHouse

The ClickHouse target builds a `WHERE` string and its parameters for you to embed in your own SQL
(placeholders use the [`smi2/phpclickhouse`](https://github.com/smi2/phpClickHouse) format):

```php
use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\ClickHouseTarget;

$ruler = new Ruler(new ClickHouseTarget());

$query = new ClickHouseQuery();
$ruler->apply($query, 'shop = :shop and amount > :amount', [
    'shop'   => 'foo',
    'amount' => 100,
]);

$where      = $query->getWhere();       // ((shop = :shop) AND (amount > :amount))
$parameters = $query->getParameters();  // ['shop' => 'foo', 'amount' => 100]
```

### Multiple targets at once

Wrap several targets in `Targets` and reuse one `Ruler` for all of them; it picks the right
executor by the query object you pass:

```php
use FiveLab\Component\Ruler\Target\Targets;

$ruler = new Ruler(new Targets(
    new DoctrineOrmTarget(),
    new ElasticaTarget(),
    new ClickHouseTarget()
));

$ruler->apply($doctrineQb, $rule, $params);
$ruler->apply($elasticaQuery, $rule, $params);
```

Rule syntax
-----------

A rule is a string of conditions combined with `and` / `or`. **Values are always passed as named
parameters** (`:name`); the array you pass to `apply()` provides them.

### Operators

| Category   | Operators                          |
|------------|------------------------------------|
| Comparison | `=`, `!=`, `<`, `<=`, `>`, `>=`     |
| Set        | `in (:param)`, `not in (:param)`   |
| Text       | `like`                             |
| Logical    | `and`, `or`                        |
| Arithmetic | `+`, `-`, `*`, `/`                 |

* **Grouping:** use parentheses — `(a = :a or b = :b) and c > :c`.
* **Arithmetic** (`+`, `-`, `*`, `/`) is available for the SQL targets (Doctrine ORM, ClickHouse).
* **Constants:** integers, floats, `true`, `false` and `null` may be written inline
  (`published = true`, `price > 100`).
* **Null:** `field = null` / `field != null` become `IS NULL` / `IS NOT NULL` for SQL targets and an
  `exists` check for Elasticsearch (it has no `NULL`).
* **Nested paths:** a dot builds joins (Doctrine) or a nested query (Elasticsearch). To treat a dot
  as part of the field name, escape it: `money\.amount`.

### Good to know

A few current constraints of the parser:

* Operators must be surrounded by spaces: `price > :price`, not `price>:price`.
* String and negative-number values cannot be written inline — pass them as parameters
  (`name = :name`, not `name = 'John'`; `price > :min`, not `price > -5`).
* Elasticsearch nested paths support a single level (`variants.name`).

Specifications
--------------

Rules can be wrapped in reusable, composable specifications and applied with `applySpec()`:

```php
use FiveLab\Component\Ruler\Specification\AndX;
use FiveLab\Component\Ruler\Specification\SimpleSpecification;

$specification = new AndX(
    new SimpleSpecification('shop = :shop', ['shop' => 'foo']),
    new SimpleSpecification('amount > :amount', ['amount' => 100])
);

$ruler->applySpec($query, $specification);
```

`OrX`, `EmptySpecification` and `TargetableSpecification` (a single specification that carries a
different rule per target, resolved with `SpecificationFilter::filterByTarget()`) are available too.

Extending
---------

* **Custom operators** — implement `OperatorsConfiguratorInterface` and register handlers on the
  `Operators` collection.
* **Custom targets** — implement `TargetInterface` (or `IdentifiableTargetInterface`) to support
  another query builder.

Security
--------

* **Rule values are safe.** Values from the parameters array are passed to the backend as bound
  parameters (SQL) or structured values (Elasticsearch) — never concatenated into the query string —
  so they are safe from injection.
* **The rule string is code, not input.** Field names and operators from the rule string are
  interpreted and written into the query. Do **not** build the rule string from untrusted user
  input. If users drive the filtering, keep the rule template static and let them supply only
  parameter *values*, or validate field names against an allow-list.

To report a vulnerability, see [SECURITY.md](SECURITY.md).

Migrating from RulerZ
---------------------

[`kphoen/rulerz`](https://github.com/K-Phoen/rulerz) solves a similar problem but has had no release
since 2018. Ruler is a maintained alternative for the Doctrine ORM and Elasticsearch use cases, with
ClickHouse support added. A few differences to keep in mind when migrating:

* Ruler **mutates your query object in place** via `apply()` (RulerZ returns filtered results from
  `filter()`); you keep full control of the query builder.
* Values must be passed as **named parameters** — inline literals in the rule string are not
  supported.
* Ruler has no in-memory/array target yet; it targets query builders (Doctrine ORM, Elasticsearch,
  ClickHouse).

Requirements
------------

* PHP `~8.2`
* `doctrine/orm` `~2.10 || ~3.0` — for the Doctrine ORM target
* `ruflin/elastica` `~7.3` — for the Elasticsearch / OpenSearch target

Development
-----------

For easy development you can use `Docker`.

```bash
docker build -t ruler .
docker run -it -v $(pwd):/code --name ruler ruler bash
```

After the container starts, install the vendors:

```bash
composer update
```

Before opening a PR, please run the checks:

```bash
./bin/phpunit

./bin/phpstan

./bin/phpcs --standard=src/phpcs.xml src/
./bin/phpcs --standard=tests/phpcs.xml tests/
```

License
-------

Ruler is released under the [MIT License](LICENSE).
