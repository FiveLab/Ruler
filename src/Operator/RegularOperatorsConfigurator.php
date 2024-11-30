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

readonly class RegularOperatorsConfigurator implements OperatorsConfiguratorInterface
{
    public function configure(Operators $operators): void
    {
        $operators->add('=', static function ($a, $b) {
            return $a.' = '.$b;
        });

        $operators->add('!=', static function ($a, $b) {
            return $a.' != '.$b;
        });

        $operators->add('>', static function ($a, $b) {
            return $a.' > '.$b;
        });

        $operators->add('>=', static function ($a, $b) {
            return $a.' >= '.$b;
        });

        $operators->add('<', static function ($a, $b) {
            return $a.' < '.$b;
        });

        $operators->add('<=', static function ($a, $b) {
            return $a.' <= '.$b;
        });

        $operators->add('+', static function ($a, $b) {
            return $a.' + '.$b;
        });

        $operators->add('-', static function ($a, $b) {
            return $a.' - '.$b;
        });

        $operators->add('*', static function ($a, $b) {
            return $a.' * '.$b;
        });

        $operators->add('/', static function ($a, $b) {
            return $a.' / '.$b;
        });
    }
}
