<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\ErrorCorrectionLevel;

/**
 * QR code.
 */
class QrCode
{
    const NUM_MASK_PATTERNS = 8;

    protected $mode;

    protected $errorCorrectionLevel;

    protected $version;

    protected $maskPattern = -1;

    protected $matrix;

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getErrorCorrectionLevel()
    {
        return $this->errorCorrectionLevel;
    }

    public function setErrorCorrectionLevel(ErrorCorrectionLevel $errorCorrectionLevel)
    {
        $this->errorCorrectionLevel = $errorCorrectionLevel;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getMaskPattern()
    {
        return $this->maskPattern;
    }

    public function setMaskPattern($maskPattern)
    {
        $this->maskPattern = $maskPattern;
    }

    public function getMatrix()
    {
        return $this->matrix;
    }

    public function setMatrix(ByteMatrix $matrix)
    {
        $this->matrix = $matrix;
    }

    public static function isValidMaskPattern($maskPattern)
    {
        return $maskPattern > 0 && $maskPattern < self::NUM_MASK_PATTERNS;
    }
}
