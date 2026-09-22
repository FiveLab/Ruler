Rule syntax
===========

A rule is a single boolean expression written as a string:

```text
category.key in (:categories) and (price > :price or featured = true)
```

It is parsed into a tree and then translated by a target into a Doctrine DQL condition, an
Elasticsearch query or a ClickHouse `WHERE` clause. This page describes the syntax itself; how each
backend translates it is covered in [Targets](targets.md).

* [Building blocks](#building-blocks)
* [Operators and precedence](#operators-and-precedence)
* [Whitespace](#whitespace)
* [Fields and paths](#fields-and-paths)
* [Parameters](#parameters)
* [Constants](#constants)
* [Null checks](#null-checks)
* [Errors](#errors)
* [Not supported](#not-supported)

Building blocks
---------------

A rule is made of five kinds of tokens:

| Token       | Example                        | Notes                                                        |
|-------------|--------------------------------|--------------------------------------------------------------|
| Field       | `price`, `category.key`        | A field or a dotted path to a field.                         |
| Parameter   | `:price`                       | A value from the parameters array passed to `apply()`.       |
| Constant    | `100`, `1.5`, `true`, `null`   | Numbers, `true`, `false` and `null` written inline.          |
| Operator    | `=`, `in`, `and`, `+`          | All operators are binary: `left operator right`.             |
| Parentheses | `(` `)`                        | Grouping. Must be balanced.                                  |

The grammar in short (operator precedence is described below):

```text
rule      = operand { operator operand }
operand   = field | parameter | number | "true" | "false" | "null" | "(" rule ")"
operator  = "or" | "and"
          | "=" | "!=" | "<" | "<=" | ">" | ">=" | "in" | "not in" | "like"
          | "+" | "-" | "*" | "/"
```

Operators and precedence
------------------------

Operators bind from the highest precedence to the lowest. Operators with the same precedence are
evaluated left to right.

| Precedence  | Operators                                            | Meaning                    |
|-------------|------------------------------------------------------|----------------------------|
| 60 (higher) | `*`, `/`                                             | Multiplication, division   |
| 30          | `+`, `-`                                             | Addition, subtraction      |
| 20          | `=`, `!=`, `<`, `<=`, `>`, `>=`, `in`, `not in`, `like` | Comparison              |
| 15          | `and`                                                | Logical AND                |
| 10 (lower)  | `or`                                                 | Logical OR                 |

Word operators are case-insensitive: `and`, `AND` and `And` are the same operator.

Examples of how rules are grouped:

| Rule                                 | Is read as                               |
|--------------------------------------|------------------------------------------|
| `a = :a or b = :b and c = :c`        | `a = :a or (b = :b and c = :c)`          |
| `(a = :a or b = :b) and c = :c`      | `(a = :a or b = :b) and c = :c`          |
| `price + tax * 2 > :limit`           | `(price + (tax * 2)) > :limit`           |
| `a - b - c = 0`                      | `((a - b) - c) = 0`                      |

> `and` binds tighter than `or`, as in SQL. Use parentheses whenever you mix them: it is easier to
> read and it does not depend on the precedence table.

### Comparison

`=`, `!=`, `<`, `<=`, `>`, `>=` compare a field with a parameter or a constant. The SQL targets
write both sides into the query as is, so there you can also compare two fields
(`updated_at > created_at`), use arithmetic or put the field on the right (`:max > price`).
Elasticsearch builds a query clause for a field, so **keep the field on the left and a value on the
right**: `price > :min`.

### `in` and `not in`

The right side is a parameter that holds a list of values:

```php
$ruler->apply($query, 'status in (:statuses)', ['statuses' => ['new', 'paid']]);
```

The parentheses around the parameter are optional: `status in :statuses` is the same rule. A list
cannot be written inline — `status in (:a, :b)` or `status in (1, 2)` are syntax errors; put the
values into one array parameter.

Do not pass an empty array: the backends disagree on what it means (with Doctrine ORM `not in`
then matches nothing, with Elasticsearch it matches everything). Skip the condition instead.

### `like`

`like` passes the pattern to the backend as is, and **the wildcard syntax depends on the target**:
`%` and `_` for SQL (Doctrine ORM, ClickHouse), `*` and `?` for Elasticsearch (a `wildcard` query).
Keep the pattern in a parameter and build it for the target you use:

```php
$ruler->apply($qb, 'name like :name', ['name' => '%phone%']);    // Doctrine ORM, ClickHouse
$ruler->apply($query, 'name like :name', ['name' => '*phone*']); // Elasticsearch
```

If the pattern is built from user input, escape the wildcard characters in it: a parameter is safe
from injection, but a `%` or `*` typed by a user still works as a wildcard. Case sensitivity is also
up to the backend: the column collation for Doctrine ORM, a case-sensitive `LIKE` in ClickHouse, the
field mapping in Elasticsearch.

### Arithmetic

`+`, `-`, `*` and `/` are available for the SQL targets (Doctrine ORM and ClickHouse) and are written
into the query as is:

```text
price * quantity > :minTotal
```

The Elasticsearch target does not support them and throws a `RuntimeException`
(`The operator "+" was not found`).

### `and` and `or`

Combine conditions. There is no unary `not`; see [Not supported](#not-supported) for alternatives.

Whitespace
----------

An operator **must be followed by whitespace**. Before a symbol operator (`=`, `>`, `+`, …) the
space is optional, but a word operator (`and`, `or`, `in`, `not in`, `like`) must be separated from
the field or parameter before it — otherwise it is read as a part of that name, and
`a = :aand b = :b` is a syntax error. The simplest rule is to always put spaces around operators:

| Rule          | Result                         |
|---------------|--------------------------------|
| `price > 100` | valid                          |
| `price> 100`  | valid (but hard to read)       |
| `price >100`  | syntax error                   |
| `price>100`   | syntax error                   |

Spaces, tabs and line breaks are all treated as whitespace, so long rules can be split over several
lines:

```php
$rule = <<<RULE
    category.key in (:categories)
    and price > :price
    and (tag = :tag or featured = true)
RULE;
```

The only exception is `not in`: exactly one whitespace character must separate the words. Two
spaces or a Windows line break (`\r\n`) between `not` and `in` are a syntax error.

Fields and paths
----------------

A field name may contain Latin letters, digits, `_`, `.` and `\` (for escaping a dot), and must not
start with a digit.

The words `and`, `or`, `in` and `like` are read as operators, and `true`, `false` and `null` as
constants (in any letter case), so fields with these names cannot be used in a rule.

### Dotted paths

A dot splits a field into a path. What a path means depends on the target:

| Target        | `category.key` becomes                                                           |
|---------------|----------------------------------------------------------------------------------|
| Doctrine ORM  | A `LEFT JOIN` on the `category` association (or a field of an embeddable).       |
| Elasticsearch | A `nested` query with the path `category` (only one nesting level).              |
| ClickHouse    | Written as is: ClickHouse resolves the dot itself (table alias, nested column).  |

See [Targets](targets.md) for the details of each backend.

### Escaping a dot

When a dot is a part of the field name rather than a path separator, escape it with a backslash:

```text
price\.amount > :amount
```

This is useful for Elasticsearch — `name\.keyword = :name` queries the `name.keyword` sub-field
instead of building a nested query — and for ClickHouse, where the name is quoted with backticks
(`` `price.amount` ``). Doctrine ORM field names cannot contain a dot, so do not escape dots there.

Remember that the backslash must reach the rule string: in PHP use single quotes
(`'price\.amount > :amount'`) or double the backslash in double quotes (`"price\\.amount > :amount"`).

Parameters
----------

A parameter is a colon followed by a name of Latin letters, digits and `_`: `:price`, `:min_price`,
`:tag2`. Values come from the array passed to `apply()` (or from a specification):

```php
$ruler->apply($query, 'price >= :min and price <= :max', ['min' => 10, 'max' => 100]);
```

Ruler never writes parameter values into query text. The SQL targets leave `:name` placeholders,
which Doctrine binds as query parameters and your ClickHouse client replaces with escaped values;
for Elasticsearch the values are placed into the query structure as data. This is what makes rules
safe to use with untrusted values — see the Security section of the [README](../README.md).

A few things to keep in mind:

* **Pass every parameter the rule uses.** The Elasticsearch target throws a `LogicException` for a
  missing parameter right away, Doctrine throws a `QueryException` when the query is executed, and
  for ClickHouse the placeholder stays in the SQL, so the server rejects the query.
* **Do not pass extra parameters to Doctrine ORM.** All entries of the array are set on the query
  builder, and Doctrine refuses to execute a query with parameters it does not use
  ("Too many parameters").
* **Use unique parameter names** when you call `apply()` several times on one query. Doctrine ORM
  sets parameters by name, so a new value silently replaces the old one (including a parameter you
  set on the query builder yourself) and changes the earlier condition. `ClickHouseQuery` throws a
  `LogicException` instead.
* **Elasticsearch expects plain values:** scalars and lists of scalars. See
  [Targets](targets.md#values) for dates, enums and filtered arrays.

Constants
---------

| Constant       | Examples                  | Notes                                                    |
|----------------|---------------------------|----------------------------------------------------------|
| Integer        | `0`, `42`, `100500`       | Digits only.                                             |
| Decimal        | `1.5`, `0.25`             | A dot between digits. `1e3` is a syntax error, `.5` builds a broken query — write `0.5`. |
| Boolean        | `true`, `false`           | Case-insensitive.                                        |
| Null           | `null`                    | Case-insensitive. See [Null checks](#null-checks).       |

For the SQL targets constants are written into the query as is (`price > 100`,
`published = true`); for Elasticsearch they become typed JSON values.

Strings and negative numbers cannot be written inline — use a parameter:

```text
name = :name        instead of   name = 'John'
balance > :min      instead of   balance > -5
```

Null checks
-----------

Compare a field with the `null` constant to check for a missing value:

| Rule            | Doctrine ORM / ClickHouse  | Elasticsearch                              |
|-----------------|----------------------------|--------------------------------------------|
| `field = null`  | `field IS NULL`            | `bool.must_not` + `exists` (field missing) |
| `field != null` | `field IS NOT NULL`        | `exists`                                   |

A **parameter** with a `null` value is not a null check for the SQL targets. With Doctrine ORM,
`field = :value` and `['value' => null]` compares with `NULL` and never matches anything; for
ClickHouse, phpClickHouse leaves the `:value` placeholder in the SQL and the query fails. Write `null`
inline for null checks. (The Elasticsearch target turns a `null` parameter into the same `exists`
check as the constant.)

Errors
------

A rule that cannot be parsed throws `FiveLab\Component\Ruler\Parser\SyntaxException`. The message
contains the position and the whole rule, and the exception exposes them as the `$cursor` and
`$expression` properties. The position is approximate (an unexpected character is counted from 0,
an unexpected token from 1), and line breaks and tabs in `$expression` are replaced by spaces:

```text
Unexpected charset "=" around position 5 for expression "price=100".
Unexpected token "property" of value "b" around position 7 for expression "a = 1 b = 2".
Unclosed "(" around position 0 for expression "(a = 1".
Unexpected ")" around position 5 for expression "a = 1)".
```

A rule that is valid but cannot be translated by a target throws at `apply()` time, for example
`RuntimeException: Only one nested level supported.` for a deep path in Elasticsearch, or
`LogicException: The part "foo" in path "foo.bar" is no an association and not embeddable.` for an
unknown association in a Doctrine path.

Ruler does not check field names against your entities or mapping. An unknown field
(`nosuchfield = :v`, `category.foo = :v`) passes `apply()`: Doctrine ORM and ClickHouse report it
only when the query runs, and Elasticsearch silently queries a field that does not exist.

Not supported
-------------

The parser deliberately stays small. The following constructs are not supported yet, but most of
them have a simple equivalent:

| Instead of               | Write                                            |
|--------------------------|--------------------------------------------------|
| `name = 'John'`          | `name = :name` with `['name' => 'John']`         |
| `balance > -5`           | `balance > :min` with `['min' => -5]`            |
| `id in (1, 2, 3)`        | `id in (:ids)` with `['ids' => [1, 2, 3]]`       |
| `field is null`          | `field = null`                                   |
| `field is not null`      | `field != null`                                  |
| `field <> :value`        | `field != :value`                                |
| `price between :a and :b`| `price >= :a and price <= :b`                    |
| `not (a = :a or b = :b)` | `a != :a and b != :b`                            |
| `price>100`              | `price > 100`                                    |

There is no equivalent for `not like` and for function calls (`lower(name)`, `now()`); to change
how an existing operator is translated, see [Extending](extending.md).
