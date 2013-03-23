<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

/**
 * General bit utilities.
 */
class BitUtils
{
    /**
     * Perform an unsigned right shift.
     *
     * This is the same as the unsigned right shift operator ">>>" in other
     * languages.
     *
     * @param  integer $a
     * @param  integer $b
     * @return integer
     */
    public static function unsignedRightShift($a, $b)
    {
        return (
            $a >= 0
            ? $a >> $b
            : (($a & 0x7fffffff) >> $b) | (0x40000000 >> ($b - 1))
        );
    }
}