Changelog
=========

Unreleased
----------

* **Security:** fixed arbitrary PHP function invocation when a property name matches a function
  name (e.g. `date`, `count`, `exec`). Now only nested-query closures are called, plain field
  names are never treated as callable.

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
