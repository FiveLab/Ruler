Extending
=========

Ruler has two extension points: **operators** (how an operator is translated for a backend) and
**targets** (which query objects a rule can be applied to). Both are small interfaces.

* [How a rule is applied](#how-a-rule-is-applied)
* [Custom operator handlers](#custom-operator-handlers)
* [Custom targets](#custom-targets)

How a rule is applied
---------------------

```text
rule string ──Lexer──▶ tokens ──Parser──▶ node tree ──Target──▶ Executor ──▶ query object
                                                                   │
                                                          Operators (per target)
```

1. `Lexer` splits the rule into tokens and `Parser` builds a tree of nodes
   (see [Rule syntax](rule-syntax.md)).
2. The target (`TargetInterface`) creates an executor for the query object and configures the
   `Operators` collection for its backend.
3. The executor walks the tree and asks `Operators` to translate every binary node, then applies
   the result to the query object.

The tree has four node types, all in the `FiveLab\Component\Ruler\Node` namespace:

| Node            | Properties                          | Written in a rule as              |
|-----------------|-------------------------------------|-----------------------------------|
| `BinaryNode`    | `$operator`, `$left`, `$right`      | `left operator right`             |
| `NameNode`      | `$name`, `getSplittedParts()`       | `price`, `category.key`           |
| `ParameterNode` | `$name` (without the colon)         | `:price`                          |
| `ConstantNode`  | `$value` (`int`, `float`, `bool`, `null`) | `100`, `true`, `null`       |

`NameNode::getSplittedParts()` splits the path by dots and resolves escaped dots:
`category.key` gives `['category', 'key']`, `price\.amount` gives `['price.amount']`.

Custom operator handlers
------------------------

An operator handler is a closure that receives the already translated left and right sides and
returns the translation of the whole operation — a string for the SQL targets, an array (a query
clause) for Elasticsearch:

```php
$operators->add('like', static fn (string $field, string $value): string => $field.' ILIKE '.$value);
```

Handlers are grouped in configurators that implement `OperatorsConfiguratorInterface`:

```php
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfiguratorInterface;

final readonly class CaseInsensitiveLikeConfigurator implements OperatorsConfiguratorInterface
{
    public function configure(Operators $operators): void
    {
        $operators->add('like', static fn (string $field, string $value): string => $field.' ILIKE '.$value);
    }
}
```

### Handler order and fallback

Several handlers can be registered for one operator. **The handler added last is tried first**, and
a handler that returns `null` passes the operation to the previous one. This is how the built-in
SQL configurator turns `= null` into `IS NULL` on top of the plain `=`:

```php
$operators->add('=', static function (string $field, string $value): ?string {
    if ('null' === \strtolower($value)) {
        return $field.' IS NULL';
    }

    return null; // Not our case: let the previous handler build "field = value".
});
```

If every handler returns `null`, `Operators` throws a `RuntimeException`.

### Using a configurator

The built-in targets create and configure their operators inside `createExecutor()`, so a custom
configurator is plugged in with a small target of your own that reuses the built-in executor:

```php
use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseExecutor;
use FiveLab\Component\Ruler\Executor\ClickHouse\ClickHouseVisitor;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfigurator;
use FiveLab\Component\Ruler\Query\ClickHouseQuery;
use FiveLab\Component\Ruler\Target\TargetInterface;

final readonly class CaseInsensitiveClickHouseTarget implements TargetInterface
{
    public function supports(object $target): bool
    {
        return $target instanceof ClickHouseQuery;
    }

    public function createExecutor(object $target): ExecutorInterface
    {
        $operators = new Operators([]);

        OperatorsConfigurator::forSql()->configure($operators);

        // Added after the SQL operators, so its "like" handler is tried first.
        (new CaseInsensitiveLikeConfigurator())->configure($operators);

        return new ClickHouseExecutor(new ClickHouseVisitor(), $operators);
    }
}

$ruler = new Ruler(new CaseInsensitiveClickHouseTarget());
$ruler->apply($query, 'name like :name', ['name' => '%phone%']); // (name ILIKE :name)
```

The same approach works for Doctrine ORM (`DoctrineOrmExecutor` + `DoctrineOrmVisitor`, which
takes the entity manager of the query builder) and Elasticsearch (`ElasticaExecutor` +
`ElasticaVisitor`, configured with `OperatorsConfigurator::forElasticSearch()`). Keep in mind that:

* a handler must return what the backend understands — `ILIKE` is not DQL, so for Doctrine ORM
  write, for example, `'LOWER('.$field.') LIKE LOWER('.$value.')'`;
* a Doctrine ORM target should implement `IdentifiableTargetInterface` like `DoctrineOrmTarget`
  does, otherwise `Targets` reuses the executor built for the first entity manager (see
  [Caching executors](#caching-executors-identifiabletargetinterface)).

For Elasticsearch nested paths you do not need to handle nesting in the handler: it receives the
full field name (`variants.color`), and the result is wrapped into the `nested` query for you.

### Limits

Handlers change **how an existing operator is translated**; they cannot add new operator words. The
operators the parser recognizes are fixed (see the precedence table in
[Rule syntax](rule-syntax.md#operators-and-precedence)), so `a ilike :b` or `a between :x and :y`
stay syntax errors whatever handlers are registered.

Custom targets
--------------

A target makes Ruler work with another query object. It consists of:

* a **target** (`TargetInterface`) — says which query objects it supports and creates an executor;
* an **executor** (`ExecutorInterface`) — applies a parsed rule to the query object;
* usually a **visitor** — walks the node tree and translates it with the operators.

Here is a complete target for the Doctrine DBAL query builder, reusing the SQL operators:

```php
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use FiveLab\Component\Ruler\Executor\ExecutorInterface;
use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;
use FiveLab\Component\Ruler\Operator\Operators;
use FiveLab\Component\Ruler\Operator\OperatorsConfigurator;
use FiveLab\Component\Ruler\Target\TargetInterface;

final readonly class DbalVisitor
{
    public function visit(Node $node, Operators $operators): string
    {
        return match (true) {
            $node instanceof BinaryNode    => '('.$operators->get($node->operator)(
                $this->visit($node->left, $operators),
                $this->visit($node->right, $operators)
            ).')',
            $node instanceof NameNode      => $node->name,
            $node instanceof ParameterNode => ':'.$node->name,
            $node instanceof ConstantNode  => (string) $node,
            default                        => throw new \InvalidArgumentException(\sprintf('Unknown node "%s".', $node::class)),
        };
    }
}

/**
 * @implements ExecutorInterface<QueryBuilder>
 */
final readonly class DbalExecutor implements ExecutorInterface
{
    public function __construct(private DbalVisitor $visitor, private Operators $operators)
    {
    }

    public function execute(object $target, Node $node, array $parameters): void
    {
        $target->andWhere($this->visitor->visit($node, $this->operators));

        foreach ($parameters as $name => $value) {
            // DBAL expands a list for "in" only when the array type is given.
            $target->setParameter($name, $value, \is_array($value) ? ArrayParameterType::STRING : ParameterType::STRING);
        }
    }
}

/**
 * @implements TargetInterface<QueryBuilder>
 */
final readonly class DbalTarget implements TargetInterface
{
    public function supports(object $target): bool
    {
        return $target instanceof QueryBuilder;
    }

    public function createExecutor(object $target): ExecutorInterface
    {
        $operators = new Operators([]);

        OperatorsConfigurator::forSql()->configure($operators);

        return new DbalExecutor(new DbalVisitor(), $operators);
    }
}
```

```php
$qb = $connection->createQueryBuilder()->select('*')->from('products');

$ruler = new Ruler(new DbalTarget());
$ruler->apply($qb, 'price > :price and (tag = :tag or deleted_at = null)', ['price' => 100, 'tag' => 'sale']);

// SELECT * FROM products WHERE ((price > :price) AND ((tag = :tag) OR (deleted_at IS NULL)))
```

Field names reach the query as written in the rule. This is fine as long as the rule string is
trusted code (see the Security section of the [README](../README.md)); if your target may receive
rules from elsewhere, check `NameNode::$name` against an allow-list in the visitor.

### Caching executors: `IdentifiableTargetInterface`

`Targets` creates an executor once per query class and reuses it. If the executor depends on the
particular query object, implement `IdentifiableTargetInterface` and return an identifier from
`getIdentifier()` — executors are then cached per class **and** identifier. The Doctrine ORM target
does this with the entity manager of the query builder, because its executor reads the entity
metadata.

### Registering

Pass the target to `Ruler` directly, or together with the built-in ones through `Targets`. `Targets`
uses the first target that supports the query object, so a target that replaces a built-in one for
the same query class (like `CaseInsensitiveClickHouseTarget` above) must come before it:

```php
$ruler = new Ruler(new Targets(
    new DoctrineOrmTarget(),
    new DbalTarget(),
    new ElasticaTarget()
));
```
