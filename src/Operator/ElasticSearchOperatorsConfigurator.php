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

namespace FiveLab\Component\Ruler\Operator;

class ElasticSearchOperatorsConfigurator implements OperatorsConfiguratorInterface
{
    public function configure(Operators $operators): void
    {
        $operators->add('=', self::makeTermCallableForOperator('must', 'term'));
        $operators->add('!=', self::makeTermCallableForOperator('must_not', 'term'));
        $operators->add('in', self::makeTermCallableForOperator('must', 'terms', false));
        $operators->add('not in', self::makeTermCallableForOperator('must_not', 'terms', false));

        $operators->add('>=', self::makeRangeCallableForOperator('gte'));
        $operators->add('>', self::makeRangeCallableForOperator('gt'));
        $operators->add('<=', self::makeRangeCallableForOperator('lte'));
        $operators->add('<', self::makeRangeCallableForOperator('lt'));

        $operators->add('and', static function ($a, $b) {
            return [
                'bool' => [
                    'must' => [$a, $b],
                ],
            ];
        });

        $operators->add('or', static function ($a, $b) {
            return [
                'bool' => [
                    'should'               => [$a, $b],
                    'minimum_should_match' => 1,
                ],
            ];
        });

        $operators->add('like', static function ($a, $b) {
            return [
                'wildcard' => [
                    $a => [
                        'value' => $b,
                    ],
                ],
            ];
        });
    }

    private static function makeRangeCallableForOperator(string $esOperator): \Closure
    {
        return static function ($a, $b) use ($esOperator) {
            return [
                'range' => [
                    $a => [
                        $esOperator => $b,
                    ],
                ],
            ];
        };
    }

    private static function makeTermCallableForOperator(string $esOperator, string $filterOperator, bool $useValueKey = true): \Closure
    {
        return static function ($a, $b) use ($esOperator, $filterOperator, $useValueKey) {
            $valueEntry = $useValueKey ? ['value' => $b] : $b;

            return [
                'bool' => [
                    $esOperator => [
                        [
                            $filterOperator => [
                                $a => $valueEntry,
                            ],
                        ],
                    ],
                ],
            ];
        };
    }
}
