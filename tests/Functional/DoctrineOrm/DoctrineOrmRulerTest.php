<?php

/*
 * This file is part of the FiveLab Ruler package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

declare(strict_types = 1);

namespace FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Ruler;
use FiveLab\Component\Ruler\Target\DoctrineOrmTarget;
use FiveLab\Component\Ruler\Tests\Functional\DoctrineOrm\Entities\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DoctrineOrmRulerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private Ruler $ruler;

    protected function setUp(): void
    {
        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new AttributeDriver([__DIR__.'/Entities']));
        $configuration->setProxyDir(\sys_get_temp_dir().'/Proxy');
        $configuration->setProxyNamespace('Proxy');
        $configuration->setAutoGenerateProxyClasses(false);

        $emConstructorRef = new \ReflectionMethod(EntityManager::class, '__construct');

        if (\method_exists(EntityManager::class, 'create')) {
            $connection = new Connection([
                'platform' => new MySqlPlatform(),
            ], new Driver());

            $this->entityManager = EntityManager::create($connection, $configuration);
        } else {
            $this->entityManager = new EntityManager($this->createMock(Connection::class), $configuration);
        }

        $this->ruler = new Ruler(new DoctrineOrmTarget());
    }

    #[Test]
    #[DataProvider('provideDataForApply')]
    public function shouldSuccessApply(string $rule, array $params, string $expectedWhereSql, array $joins = []): void
    {
        $qb = (new QueryBuilder($this->entityManager))
            ->from(Product::class, 'products')
            ->select('products');

        $this->ruler->apply($qb, $rule, $params);

        $qbParameters = [];

        /** @var Parameter $parameter */
        foreach ($qb->getParameters() as $parameter) {
            $qbParameters[$parameter->getName()] = $parameter->getValue();
        }

        self::assertEquals($expectedWhereSql, (string) $qb->getDQLPart('where'));
        self::assertEquals($params, $qbParameters);
        self::assertEquals($joins, $qb->getDQLPart('join'));

        // Try to get query for check correct DQL
        $qb->getQuery();

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function shouldThrowErrorIfRelationAndEmbeddedNotFound(): void
    {
        $qb = (new QueryBuilder($this->entityManager))
            ->from(Product::class, 'products')
            ->select('products');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The part "foo" in path "foo.bar" is no an association and not embeddable.');

        $this->ruler->apply($qb, 'foo.bar', []);
    }

    public static function provideDataForApply(): array
    {
        return [
            'eq' => [
                'id = :id',
                ['id' => 123],
                '(products.id = :id)',
            ],

            'not eq' => [
                'id != :id',
                ['id' => '321'],
                '(products.id != :id)',
            ],

            'gt' => [
                'price > :price',
                ['price' => 150],
                '(products.price > :price)',
            ],

            'gte' => [
                'price >= :price',
                ['price' => 200],
                '(products.price >= :price)',
            ],

            'lt' => [
                'price < :price',
                ['price' => 100],
                '(products.price < :price)',
            ],

            'lte' => [
                'price <= :price',
                ['price' => 200],
                '(products.price <= :price)',
            ],

            'in' => [
                'tag IN (:possible_tags)',
                ['possible_tags' => ['foo', 'bar']],
                '(products.tag IN (:possible_tags))',
            ],

            'not in' => [
                'tag NOT IN (:possible_tags)',
                ['possible_tags' => ['foo', 'bar']],
                '(products.tag NOT IN (:possible_tags))',
            ],

            'eq null' => [
                'tag = null',
                [],
                '(products.tag IS NULL)',
            ],

            'not eq null' => [
                'tag != null',
                [],
                '(products.tag IS NOT NULL)',
            ],

            'like' => [
                'tag like :tag',
                ['tag' => '%foo%'],
                '(products.tag LIKE :tag)',
            ],

            'one join' => [
                'category.key = :cat',
                ['cat' => 'foo'],
                '(category.key = :cat)',
                [
                    'products' => [
                        new Join('LEFT', 'products.category', 'category'),
                    ],
                ],
            ],

            'one join with multiple fields' => [
                'category.key = :cat AND category.enabled = :enabled',
                ['cat' => 'foo', 'enabled' => true],
                '((category.key = :cat) AND (category.enabled = :enabled))',
                [
                    'products' => [
                        new Join('LEFT', 'products.category', 'category'),
                    ],
                ],
            ],

            'nested join' => [
                'variants.category.key = :cat',
                ['cat' => 'foo'],
                '(variants_category.key = :cat)',
                [
                    'products' => [
                        new Join('LEFT', 'products.variants', 'variants'),
                        new Join('LEFT', 'variants.category', 'variants_category'),
                    ],
                ],
            ],

            'multiple joins with nested' => [
                'category.key = :cat OR variants.category.enabled = :enabled',
                ['cat' => 'foo', 'enabled' => true],
                '((category.key = :cat) OR (variants_category.enabled = :enabled))',
                [
                    'products' => [
                        new Join('LEFT', 'products.category', 'category'),
                        new Join('LEFT', 'products.variants', 'variants'),
                        new Join('LEFT', 'variants.category', 'variants_category'),
                    ],
                ],
            ],

            'embedded' => [
                'amount.currency = :currency AND amount.amount > :amount',
                ['currency' => 'USD', 'amount' > 100],
                '((products.amount.currency = :currency) AND (products.amount.amount > :amount))',
            ],
        ];
    }
}
