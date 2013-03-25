<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use BaconQrCode\Exception;
use SplFixedArray;

/**
 * General array utilities.
 */
class ArrayUtils
{
    /**
     * Copies an array from the specified source array.
     *
     * @param SplFixedArray $src
     * @param integer       $srcPos
     * @param SplFixedArray $dest
     * @param integer       $destPos
     * @param integer       $length
     */
    public function arrayCopy(SplFixedArray $src, $srcPos, SplFixedArray $dest, $destPos, $length)
    {
        if ($srcPos < 0) {
            throw new Exception\OutOfBoundsException('SrcPos may not be negative');
        } elseif ($destPos < 0) {
            throw new Exception\OutOfBoundsException('DestPos may not be negative');
        } elseif ($length < 0) {
            throw new Exception\OutOfBoundsException('Length may not be negative');
        } elseif ($srcPos + $length > count($src)) {
            throw new Exception\OutOfBoundsException('SrcPos + length is greater than the length of the source array');
        } elseif ($destPos + $length > count($dest)) {
            throw new Exception\OutOfBoundsException('DestPos + length is greater than the length of the destination array');
        }

        for ($i = 0; $i < $length; $i++) {
            $dest[$destPos + $i] = $src[$srcPos + $i];
        }
    }
}