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
 * Encapsualtes the parameters for one error-correction block in one symbol
 * version. This includes the number of data codewords, and the number of times
 * a block with these parameters is used consecutively in the QR code version's
 * format.
 */
class EcBlock
{
    protected $count;
    protected $dataCodewords;

    public function __construct($count, $dataCodewords)
    {
        $this->count         = $count;
        $this->dataCodewords = $dataCodewords;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getDataCodewords()
    {
        return $this->dataCodewords;
    }
}
