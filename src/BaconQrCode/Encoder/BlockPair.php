<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

/**
 * Block pair.
 */
class BlockPair
{
    protected $dataBytes;

    protected $errorCorrectionBytes;

    public function __construct($data, $errorCorrection)
    {
        $this->dataBytes            = $data;
        $this->errorCorrectionBytes = $errorCorrection;
    }

    public function getDataBytes()
    {
        return $this->dataBytes;
    }

    public function getErrorCorrectionBytes()
    {
        return $this->errorCorrectionBytes;
    }
}
