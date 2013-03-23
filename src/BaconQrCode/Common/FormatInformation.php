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
 * Encapsulates a QR Code's format information, including the data mask used and
 * error correction level.
 */
class FormatInformation
{
    const FORMAT_INFOR_MASK_QR = 0x5412;

    protected static $formatInfoDecodeLookup = array(
        array(0x5412, 0x00),
        array(0x5125, 0x01),
        array(0x5e7c, 0x02),
        array(0x5b4b, 0x03),
        array(0x45f9, 0x04),
        array(0x40ce, 0x05),
        array(0x4f97, 0x06),
        array(0x4aa0, 0x07),
        array(0x77c4, 0x08),
        array(0x72f3, 0x09),
        array(0x7daa, 0x0a),
        array(0x789d, 0x0b),
        array(0x662f, 0x0c),
        array(0x6318, 0x0d),
        array(0x6c41, 0x0e),
        array(0x6976, 0x0f),
        array(0x1689, 0x10),
        array(0x13be, 0x11),
        array(0x1ce7, 0x12),
        array(0x19d0, 0x13),
        array(0x0762, 0x14),
        array(0x0255, 0x15),
        array(0x0d0c, 0x16),
        array(0x083b, 0x17),
        array(0x355f, 0x18),
        array(0x3068, 0x19),
        array(0x3f31, 0x1a),
        array(0x3a06, 0x1b),
        array(0x24b4, 0x1c),
        array(0x2183, 0x1d),
        array(0x2eda, 0x1e),
        array(0x2bed, 0x1f),
    );

    protected static $bitsSetInHalfByte = array(0, 1, 1, 2, 1, 2, 2, 3, 1, 2, 2, 3, 2, 3, 3, 4);

    protected $ecLevel;
    protected $dataMask;

    protected function __construct($formatInfo)
    {
        $this->ecLevel  = new ErrorCorrectionLevel(($formatInfo >> 3) & 0x03);
        $this->dataMask = $formatInfo & 0x07;
    }

    public static function numBitsDiffering($a, $b)
    {
        $a ^= $b;

        return (
            self::$bitsSetInHalfByte[$a & 0xf]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 4) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 8) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 12) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 16) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 20) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 24) & 0x0f)]
            + self::$bitsSetInHalfByte[(self::unsignedRightShift($a, 28) & 0x0f)]
        );
    }

    public static function decodeFormatInformation($maskedFormatInfo1, $maskedFormatInfo2)
    {
        $formatInfo = self::doDecodeFormatInformation($maskedFormatInfo1, $maskedFormatInfo2);

        if ($formatInfo !== null) {
            return $formatInfo;
        }

        // Should return null, but, some QR codes apparently do not mask this
        // info. Try again by actually masking the pattern first.
        return self::doDecodeFormatInformation(
            $maskedFormatInfo1 ^ self::FORMAT_INFOR_MASK_QR,
            $maskedFormatInfo2 ^ self::FORMAT_INFOR_MASK_QR
        );
    }

    protected static function doDecodeFormatInformation($maskedFormatInfo1, $maskedFormatInfo2)
    {
        $bestDifference = PHP_INT_MAX;
        $bestFormatInfo = 0;

        foreach (self::$formatInfoDecodeLookup as $decodeInfo) {
            $targetInfo = $decodeInfo[0];

            if ($targetInfo === $maskedFormatInfo1 || $targetInfo === $maskedFormatInfo2) {
                // Found an exact match
                return new self($decodeInfo[1]);
            }

            $bitsDifference = self::numBitsDiffering($maskedFormatInfo1, $targetInfo);

            if ($bitsDifference < $bestDifference) {
                $bestFormatInfo = $decodeInfo[1];
                $bestDifference = $bitsDifference;
            }

            if ($maskedFormatInfo1 !== $maskedFormatInfo2) {
                // Also try the other option
                $bitsDifference = self::numBitsDiffering($maskedFormatInfo2, $targetInfo);

                if ($bitsDifference < $bestDifference) {
                    $bestFormatInfo = $decodeInfo[1];
                    $bestDifference = $bitsDifference;
                }
            }
        }

        // Hamming distance of the 32 masked codes is 7, by construction, so
        // <= 3 bits differing means we found a match.
        if ($bestDifference <= 3) {
            return new self($bestFormatInfo);
        }

        return null;
    }

    public function getErrorCorrectionLevel()
    {
        return $this->ecLevel;
    }

    public function getDataMask()
    {
        return $this->dataMask;
    }

    public function hashCode()
    {
        return ($this->ecLevel->get() << 3) | $this->dataMask;
    }

    public function equals($other) {
        if (!$other instanceof self) {
            return false;
        }

        return (
            $this->ecLevel->get() === $other->getErrorCorrectionLevel()->get()
            && $this->dataMask === $other->getDataMask()
        );
    }

    protected static function unsignedRightShift($a, $b)
    {
        if ($b === 0) {
            return $a;
        }

        return ($a >> $b) & ~(1 << (8 * PHP_INT_SIZE - 1) >> ($b - 1));
    }
}
