Targets
=======

The same rule is translated differently for each backend. This page shows what every target builds
and where the backends behave differently. The rule syntax itself is described in
[Rule syntax](rule-syntax.md).

* [Operators by target](#operators-by-target)
* [Doctrine ORM](#doctrine-orm)
* [Elasticsearch / OpenSearch](#elasticsearch--opensearch)
* [ClickHouse](#clickhouse)
* [Several targets at once](#several-targets-at-once)

Operators by target
-------------------

| Rule                  | Doctrine ORM (DQL)         | ClickHouse (SQL)           | Elasticsearch                                   |
|-----------------------|----------------------------|----------------------------|-------------------------------------------------|
| `a = :v`              | `a = :v`                   | `a = :v`                   | `bool.must` → `term`                            |
| `a != :v`             | `a != :v`                  | `a != :v`                  | `bool.must_not` → `term`                        |
| `a < :v` … `a >= :v`  | `a < :v` …                 | `a < :v` …                 | `range` with `lt` / `lte` / `gt` / `gte`        |
| `a in (:v)`           | `a IN (:v)`                | `a IN (:v)`                | `bool.must` → `terms`                           |
| `a not in (:v)`       | `a NOT IN (:v)`            | `a NOT IN (:v)`            | `bool.must_not` → `terms`                       |
| `a like :v`           | `a LIKE :v` (`%`, `_`)     | `a LIKE :v` (`%`, `_`)     | `wildcard` (`*`, `?`)                           |
| `a = null`            | `a IS NULL`                | `a IS NULL`                | `bool.must_not` → `exists`                      |
| `a != null`           | `a IS NOT NULL`            | `a IS NOT NULL`            | `exists`                                        |
| `x and y`             | `x AND y`                  | `x AND y`                  | `bool.must: [x, y]`                             |
| `x or y`              | `x OR y`                   | `x OR y`                   | `bool.should: [x, y]`, `minimum_should_match: 1`|
| `+`, `-`, `*`, `/`    | as is                      | as is                      | not supported (`RuntimeException`)              |

Every condition built by the SQL targets is wrapped in parentheses, so the precedence of the rule
is kept in the generated query: `a = :a or b = :b and c = :c` becomes
`((a = :a) OR ((b = :b) AND (c = :c)))`.

`!=` and `not in` treat missing values differently. In SQL a `NULL` column never satisfies them,
while the Elasticsearch `must_not` also matches documents without the field. To make them agree,
exclude missing values on Elasticsearch (`a != :v and a != null`) or include them in SQL
(`(a != :v or a = null)`).

Doctrine ORM
------------

Pass a `Doctrine\ORM\QueryBuilder` with a root entity and alias:

```php
$qb = $entityManager->createQueryBuilder()
    ->select('products')
    ->from(Product::class, 'products');

$ruler->apply($qb, 'category.key in (:categories) and price > :price', [
    'categories' => ['phones', 'tablets'],
    'price'      => 100,
]);
```

`apply()` modifies the query builder in place:

* adds the condition with `andWhere()`, so several `apply()` calls (and your own `andWhere()`) are
  combined with `AND`;
* sets every entry of the parameters array with `setParameter()`. Parameters with other names stay,
  but **a parameter with the same name is replaced** — yours or one from an earlier `apply()` — which
  silently changes the condition that used it, so keep parameter names unique;
* adds the `LEFT JOIN`s the rule needs.

### Fields

A field without a dot is a field of the root entity: `price` becomes `products.price`. Do not prefix
fields with the alias yourself: `products.price` is read as an association named `products` and
throws a `LogicException`.

### Joins

A dotted path walks the associations of the root entity and joins each of them with a `LEFT JOIN`.
The join aliases are made of the association names, joined with `_` for deeper levels:

| Rule                              | Joins                                                              | Condition                        |
|-----------------------------------|--------------------------------------------------------------------|----------------------------------|
| `category.key = :key`             | `products.category category`                                       | `category.key = :key`            |
| `variants.category.key = :key`    | `products.variants variants`, `variants.category variants_category`| `variants_category.key = :key`   |

A join is added once per alias, even when the path is used several times or `apply()` is called
again. **If the query builder already has an alias with the same name, Ruler reuses it** and does
not add a join — make sure your own alias `category` really is the `category` association.

Because association names become aliases, an association named after a DQL keyword (`order`,
`group`, …) cannot be used in a path: the generated DQL is invalid.

Consequences of the joins worth knowing:

* `category.key = null` matches products whose category has no key **and** products without a
  category at all.
* All conditions on one path use the same join, so they must hold for **one** joined row:
  `variants.color = :color and variants.size = :size` finds products with a variant that has both
  this color and this size. Elasticsearch checks each condition separately (see
  [Nested fields](#nested-fields)), so the same rule can find more products there.
* A join on a to-many association (`variants.color = :color`) returns one SQL row per matching
  variant. Keep that in mind with `setMaxResults()` and pagination (Doctrine's `Paginator` handles
  it).

### Embeddables

When a part of the path is an embeddable rather than an association, no join is added:
`amount.currency = :currency` becomes `products.amount.currency = :currency`. An embeddable of a
joined entity works the same way: the associations before it are joined, the embeddable is not.
Only one embeddable level is supported: for an embeddable inside an embeddable the path is built
wrong, and Doctrine rejects the query.

A part that is neither an association nor an embeddable throws a `LogicException`:
`The part "foo" in path "foo.bar" is no an association and not embeddable.`

### Escaped dots

DQL field names cannot contain a dot, so escaped dots (`price\.amount`) do not make sense for this
target — the generated DQL is invalid. Use escaping only with Elasticsearch and ClickHouse.

### Parameters

Doctrine executes a query only when the bound parameters match the placeholders in the DQL exactly:

* a parameter missing from the array fails with `Too few parameters` when the query is executed (a
  misspelt one with `Invalid parameter: token … is not defined in the query`);
* an extra parameter that the rule does not use fails with `Too many parameters`.

When the rule is built from optional filters, pass only the parameters of the conditions you
actually added.

Elasticsearch / OpenSearch
--------------------------

Pass an `Elastica\Query` (for `ruflin/elastica`) or a `RawSearchQuery` (for the native
`elasticsearch/elasticsearch` and `opensearch-project/opensearch-php` clients):

```php
$query = new RawSearchQuery();
$query->setRawQuery(['size' => 20, 'sort' => [['price' => 'asc']]]);

$ruler->apply($query, 'price > :price and tag in (:tags)', [
    'price' => 100,
    'tags'  => ['sale', 'new'],
]);

$client->search(['index' => 'products', 'body' => $query->toArray()]);
```

`apply()` sets only the `query` part of the request, so `size`, `sort`, `aggs` and other keys are
kept. When the request already has a query, the new one is combined with it:
`{"bool": {"must": [<existing query>, <new query>]}}`.

### Values

Parameter values are put into the query as they are, so pass scalars and lists of scalars:

* an object (`DateTimeInterface`, an enum) throws a `TypeError` in `apply()` — format dates as
  strings and pass `$enum->value`;
* an array for `in` must be a list: after `array_filter()` call `array_values()`, otherwise the
  keys are kept and `terms` receives a JSON object instead of an array.

### Field on the left, value on the right

Every comparison becomes a query clause for one field, so the left side must be a field and the
right side a parameter or a constant. `price > :min` works; `:max > price` or `price > cost` do not
throw, but build a meaningless query (a clause for a "field" named after the value, or a comparison
with the string `"cost"`).

### Exact values: `term` and `terms`

`=`, `!=`, `in` and `not in` build `term` / `terms` queries: they match exact values and are meant
for `keyword`, numeric, date and boolean fields. A `text` field is analyzed, so a `term` query on it
usually finds nothing. Query its `keyword` sub-field instead, and escape the dot so that it is not
read as a nested path:

```text
name\.keyword = :name
```

The value of `in` / `not in` must be an array — `terms` does not accept a single value.

### `like`

`like` builds a `wildcard` query: `*` matches any sequence of characters and `?` a single character.
The `%` and `_` of SQL have no special meaning here. On a `text` field the pattern is matched against
single analyzed terms; to match the whole value, use the `keyword` sub-field:
`name\.keyword like :name`.

### Nested fields

A dotted path builds a `nested` query:

```text
variants.color = :color
```

```json
{"nested": {"path": "variants", "query": {"bool": {"must": [{"term": {"variants.color": {"value": "red"}}}]}}}}
```

* Only **one nesting level** is supported: `variants.options.color` throws
  `RuntimeException: Only one nested level supported.`
* **Each comparison becomes its own `nested` query.** `variants.color = :color and variants.size =
  :size` matches a product that has *some* variant of that color and *some* (possibly another)
  variant of that size — not necessarily one variant that has both. Doctrine ORM, in contrast,
  requires one joined row for both.
* `variants.color = null` matches products that have a variant without a color. Unlike the Doctrine
  `LEFT JOIN`, products without any variants do not match.
* For a field of an `object` (not `nested`) mapping, or for a multi-field, escape the dot:
  `address\.city = :city`, `name\.keyword = :name`.

### Null

Elasticsearch has no `NULL`, so `field = null` becomes "the field is missing" (`must_not` + `exists`)
and `field != null` becomes `exists`. A parameter with a `null` value is treated the same way.

### Not supported

Arithmetic operators (`+`, `-`, `*`, `/`) throw
`RuntimeException: The operator "+" was not found.` A missing parameter throws a `LogicException`
right in `apply()`.

ClickHouse
----------

ClickHouse has no query builder to modify, so the target fills a `ClickHouseQuery` with the
condition and its parameters, and you put them into your own SQL:

```php
$query = new ClickHouseQuery();

$ruler->apply($query, 'shop = :shop and amount > :amount', ['shop' => 'foo', 'amount' => 100]);
$ruler->apply($query, 'status in (:statuses)', ['statuses' => ['paid', 'refunded']]);

$sql = 'SELECT count() FROM orders WHERE '.$query->getWhere();

$client->select($sql, $query->getParameters());
```

* `getWhere()` returns the conditions of all `apply()` calls joined with `AND`, each one wrapped in
  parentheses; an empty string when nothing was applied.
* `getParameters()` returns the parameters of all calls. Placeholders use the `:name` format of
  [`smi2/phpclickhouse`](https://github.com/smi2/phpClickHouse), which replaces them with escaped
  values on the client side.
* Binding a different value to a parameter name that is already used throws a `LogicException`.

phpClickHouse leaves the placeholder in the SQL when a value is `null` (or missing), and renders
`false` as an empty string — both make the query fail. Write null checks inline (`field = null`) and
pass booleans as `1` / `0` or inline (`published = true`).

### Fields

Field names are written as is, and a dot is left to ClickHouse to resolve (a table alias, a
`Nested` column, a tuple element), so `o.amount > :amount` works with `FROM orders AS o`. An escaped
dot makes the dot a part of the name, and the name is quoted with backticks:
`price\.amount` becomes `` `price.amount` ``.

Several targets at once
-----------------------

`Targets` combines several targets in one `Ruler`, which uses the first target that supports the
query object passed to `apply()`:

```php
$ruler = new Ruler(new Targets(
    new DoctrineOrmTarget(),
    new ElasticaTarget(),
    new ClickHouseTarget()
));
```

The executor of each target is created once and reused: per query class, and for Doctrine ORM also
per entity manager. An object that no target supports throws `RuntimeException: Any target support
"<class>".`

When one rule has to look different for different backends (for example `like` patterns or
`name\.keyword` for Elasticsearch), keep the variants in a `TargetableSpecification` and pick one
with `SpecificationFilter::filterByTarget()`.
