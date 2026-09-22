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
use Doctrine\ORM\Query\Lexer as DqlLexer;
use Doctrine\ORM\QueryBuilder;
use FiveLab\Component\Ruler\Executor\ExecutionContext;
use FiveLab\Component\Ruler\Node\BinaryNode;
use FiveLab\Component\Ruler\Node\ConstantNode;
use FiveLab\Component\Ruler\Node\NameNode;
use FiveLab\Component\Ruler\Node\Node;
use FiveLab\Component\Ruler\Node\ParameterNode;
use FiveLab\Component\Ruler\Operator\Operators;

readonly class DoctrineOrmVisitor
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function visit(QueryBuilder $target, Node $node, array $parameters, Operators $operators, ExecutionContext $context): string
    {
        if ($node instanceof BinaryNode) {
            $leftSide = $this->visit($target, $node->left, $parameters, $operators, $context);
            $rightSide = $this->visit($target, $node->right, $parameters, $operators, $context);

            $operator = $operators->get($node->operator);

            return '('.$operator($leftSide, $rightSide).')';
        }

        if ($node instanceof NameNode) {
            $parts = $node->getSplittedParts();

            foreach ($parts as $part) {
                if (\str_contains($part, '.')) {
                    throw new \LogicException(\sprintf(
                        'The escaped dot in the field "%s" is not supported by the Doctrine ORM target.',
                        $node->name
                    ));
                }
            }

            if (\count($parts) > 1) {
                // Maybe join detected.
                return $this->detectJoins($target, $node, $context);
            }

            return $context->get('rootAlias').'.'.$node->name;
        }

        if ($node instanceof ParameterNode) {
            return ':'.$node->name;
        }

        if ($node instanceof ConstantNode) {
            return (string) $node;
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unknown node "%s".',
            \get_class($node)
        ));
    }

    private function detectJoins(QueryBuilder $target, NameNode $node, ExecutionContext $context): string
    {
        $parts = $node->getSplittedParts();

        $rootEntity = $target->getRootEntities()[0];
        $rootAlias = $target->getRootAliases()[0];

        $metadata = $this->entityManager->getClassMetadata($rootEntity);

        $lastField = \array_pop($parts);
        $aliasParts = [];
        $alias = null;

        while (null !== ($part = \array_shift($parts))) {
            if (!$metadata->hasAssociation($part)) {
                // Hasn't association, maybe embeddable? The rest of the path belongs to it, so that a nested
                // embeddable ("total.money.amount") keeps all its parts.
                $embeddedName = \implode('.', [$part, ...$parts, $lastField]);

                if ($metadata->hasField($embeddedName)) {
                    return ($alias ?? $rootAlias).'.'.$embeddedName;
                }

                if (\array_key_exists($part, $metadata->embeddedClasses)) {
                    throw new \LogicException(\sprintf(
                        'The path "%s" is not a field of the embeddable "%s".',
                        $node->name,
                        $part
                    ));
                }

                throw new \LogicException(\sprintf(
                    'The part "%s" in path "%s" is no an association and not embeddable.',
                    $part,
                    $node->name
                ));
            }

            $join = ($alias ?? $rootAlias).'.'.$part;

            $aliasParts[] = $part;
            $alias = self::makeAlias($aliasParts);

            $context->add('joins', null, [
                'join'  => $join,
                'alias' => $alias,
            ]);

            $association = $metadata->getAssociationMapping($part);
            $metadata = $this->entityManager->getClassMetadata($association['targetEntity']);
        }

        return ($alias ?? $rootAlias).'.'.$lastField;
    }

    private static function makeAlias(array $aliasParts): string
    {
        $alias = \implode('_', $aliasParts);

        // A DQL keyword can't be an alias, so an association named "order" or "group" gets an underscore.
        return self::isReservedWord($alias) ? $alias.'_' : $alias;
    }

    private static function isReservedWord(string $word): bool
    {
        static $identifierType = null;

        if (null === $identifierType) {
            // Ask the DQL lexer instead of keeping our own list of keywords. The identifier token type is
            // named differently in Doctrine ORM 2 and 3, so take it from a word that surely is an identifier.
            $identifierType = self::tokenType('rulerAliasProbe');
        }

        return self::tokenType($word) !== $identifierType;
    }

    private static function tokenType(string $word): mixed
    {
        $lexer = new DqlLexer($word);
        $lexer->moveNext();

        // Doctrine lexer 1 and 2 give an array, 3 gives a token object.
        $token = (array) $lexer->lookahead;

        return $token['type'] ?? null;
    }
}
