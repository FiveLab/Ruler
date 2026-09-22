Changelog
=========

Unreleased
----------

* Fixed composite specifications (`AndX`, `OrX`) breaking parameters when they rename a duplicated
  one: renaming `:price` no longer changes `:price_min`, and a generated name (`price_1`) no longer
  overwrites a parameter that already has this name.
* Fixed field names that silently lost a part and built a query for a wrong field: a `0` part
  (`items.0.price`) is kept now, and a name with an empty part (`.5`, `a..b`, `a.`) or with a
  backslash that doesn't escape a dot (`a\`, `a\b`) throws a `SyntaxException`.
* Elasticsearch target: a comparison where the left side is not a field (`:max > price`, `100 = id`),
  where both sides are fields (`price > cost`), or an `and` / `or` over something that is not a
  condition now throws a `LogicException` instead of silently building a meaningless query.
* Fixed parameter values for the Elasticsearch target: an object failed with a `TypeError` (inside
  a list it was encoded as a JSON object), and a filtered list (`array_filter`) was encoded as a
  JSON object, so `terms` silently matched nothing. Now a `DateTimeInterface` becomes an ISO 8601
  string with milliseconds, a backed enum its value, a `Stringable` a string, a list with missed
  keys is reindexed, and an array with string keys (a terms lookup) is kept as an object. Any other
  object throws a `LogicException`.

v1.4.1
------

* **Security:** fixed arbitrary PHP function invocation when a property name matches a function
  name (e.g. `date`, `count`, `exec`). Now only nested-query closures are called, plain field
  names are never treated as callable.
* Fixed Doctrine ORM target dropping parameters already set on the query builder before `apply()`.
* Fixed Doctrine ORM target adding a duplicate join alias on a repeated `apply()` (or when the
  join alias already existed on the query builder), which raised "'<alias>' is already defined".
* Fixed Elastica target overwriting the whole request body: `apply()` now keeps other query
  parts (`size`, `sort`, `aggs`, ...) and combines with an already collected query via `bool.must`
  instead of replacing it, so `apply()` can be called several times on the same query.
* Fixed `null` handling for the Elasticsearch target: a parameter passed with a `null` value is no
  longer reported as missing, and `field = null` / `field != null` now build an `exists` check
  (`must_not exists` / `exists`) instead of failing.

v1.4.0
------

* Added `FiveLab\Component\Ruler\Target\ClickHouseTarget` for build the conditions for ClickHouse.
* Added `FiveLab\Component\Ruler\Query\ClickHouseQuery` for use it as target for ClickHouse.

v1.3.1
------

* Add support PHP `8.4`.
* Fix implicit nullable parameters in `FiveLab\Component\Ruler\Ruler`.

v1.3.0
------

* Support only PHP `~8.2`.
* Refactor classes for use readonly properties.
* Fix `setParameters` for doctrine new versions.

v1.2.0
------

* Added `opensearch-project/opensearch-php` libraries to `FiveLab\Component\Ruler\Target\ElasticaTarget`.
* Added `FiveLab\Component\Ruler\Query\RawSearchQuery` for use it as target for OpenSearch.

v1.1.0
------

* Add targetable specification for possible use one top-level specification for many targets.

v1.0.1
------

* Fix `IN` and `NOT IN` condition for elasticsearch (opensearch) executor.

v1.0.0
------

Initialize library.
