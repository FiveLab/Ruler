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

namespace FiveLab\Component\Ruler\Executor\DoctrineOrm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Executor\ExecutionContext;
use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;
use FiveLab\Component\Ruler\Operator\Operators;

/**
 * The visitor for visit nodes for Doctrine ORM.
 */
class DoctrineOrmVisitor
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Visit node for target
     *
     * @param QueryBuilder         $target
     * @param Node                 $node
     * @param array<string, mixed> $parameters
     * @param Operators            $operators
     * @param ExecutionContext     $context
     *
     * @return string Returns the SQL for WHERE clause
     */
    public function visit(QueryBuilder $target, Node $node, array $parameters, Operators $operators, ExecutionContext $context): string
    {
        if ($node instanceof BinaryNode) {
            $leftSide = $this->visit($target, $node->getLeft(), $parameters, $operators, $context);
            $rightSide = $this->visit($target, $node->getRight(), $parameters, $operators, $context);

            $operator = $operators->get($node->getOperator());

            return '('.$operator($leftSide, $rightSide).')';
        }

        if ($node instanceof NameNode) {
            $name = $context->get('rootAlias').'.'.$node->getName();

            if (false !== \strpos($node->getName(), '.')) {
                // Maybe join detected.
                $name = $this->detectJoins($target, $node, $context);
            }

            return $name;
        }

        if ($node instanceof ParameterNode) {
            return ':'.$node->getName();
        }

        if ($node instanceof ConstantNode) {
            return (string) $node;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown node "%s".',
            \get_class($node)
        ));
    }

    /**
     * Try to detect join
     *
     * @param QueryBuilder     $target
     * @param NameNode         $node
     * @param ExecutionContext $context
     *
     * @return string
     */
    private function detectJoins(QueryBuilder $target, NameNode $node, ExecutionContext $context): string
    {
        $parts = $node->getSplittedParts();

        $rootEntity = $target->getRootEntities()[0];
        $rootAlias = $target->getRootAliases()[0];

        $metadata = $this->entityManager->getClassMetadata($rootEntity);

        $lastField = \array_pop($parts);
        $aliases = [];

        while ($part = \array_shift($parts)) {
            if (!$metadata->hasAssociation($part)) {
                // Hasn't association, maybe embeddable?
                if (\array_key_exists($part, $metadata->embeddedClasses)) {
                    $embeddedName = $part.'.'.$lastField;

                    return \count($aliases) ? \implode('_', $aliases).'.'.$embeddedName : $rootAlias.'.'.$embeddedName;
                }

                throw new \LogicException(\sprintf(
                    'The part "%s" in path "%s" is no an association and not embeddable.',
                    $part,
                    $node->getName()
                ));
            }

            if (!\count($aliases)) {
                // It's a first join. Join with root alias.
                $context->add('joins', null, [
                    'join'  => $context->get('rootAlias').'.'.$part,
                    'alias' => $part,
                ]);

                $aliases[] = $part;
            } else {
                $alias = \implode('_', $aliases);
                $aliases[] = $part;

                $context->add('joins', null, [
                    'join'  => $alias.'.'.$part,
                    'alias' => \implode('_', $aliases),
                ]);
            }

            $association = $metadata->getAssociationMapping($part);
            $metadata = $this->entityManager->getClassMetadata($association['targetEntity']);
        }

        return \implode('_', $aliases).'.'.$lastField;
    }
}
