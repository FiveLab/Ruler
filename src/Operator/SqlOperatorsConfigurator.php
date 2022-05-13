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

/**
 * The configurator for configure SQL like operators.
 */
class SqlOperatorsConfigurator implements OperatorsConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(Operators $operators): void
    {
        $operators->add('and', static function ($a, $b) {
            return $a.' AND '.$b;
        });

        $operators->add('or', static function ($a, $b) {
            return $a.' OR '.$b;
        });

        $operators->add('in', static function ($a, $b) {
            return $a.' IN ('.$b.')';
        });

        $operators->add('not in', static function ($a, $b) {
            return $a.' NOT IN ('.$b.')';
        });

        $operators->add('like', static function ($a, $b) {
            return $a.' LIKE '.$b;
        });

        // Override basic operators
        $operators->add('=', static function ($a, $b) {
            if (\strtolower($b) === 'null') {
                return $a.' IS NULL';
            }

            return null;
        });

        $operators->add('!=', static function ($a, $b) {
            if (\strtolower($b) === 'null') {
                return $a.' IS NOT NULL';
            }

            return null;
        });
    }
}
