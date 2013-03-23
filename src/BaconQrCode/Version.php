<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode;

use SplFixedArray;

/**
 * Version representation.
 */
class Version
{
    protected static $versionDecodeInfo = array(
        0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d,
        0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9,
        0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75,
        0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64,
        0x27541, 0x28c69
    );

    protected static $versions = array();

    protected $versionNumber;

    protected $alignmentPatternCenters;

    protected $errorCorrectionBlocks;

    protected $totalCodewords;

    protected function __construct(
        $versionNumber,
        SplFixedArray $alignmentPatternCenters,
        SplFixedArray $ecBlocks
    ) {
        $this->versionNumber           = $versionNumber;
        $this->alignmentPatternCenters = $alignmentPatternCenters;
        $this->errorCorrectionBlocks   = $ecBlocks;

        $totalCodewords = 0;
        $ecCodewords    = $ecBlocks[0]->getEcCodewordsPerBlock();

        foreach ($ecBlocks[0]->getEcBlocks() as $ecBlock) {
            $totalCodewords += $ecBlock->getCount() * ($ecBlock->getDataCodewords() + $ecCodewords);
        }

        $this->totalCodewords = $totalCodewords;
    }

    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    public function getAlignmentPatternCenters()
    {
        return $this->alignmentPatternCenters;
    }

    public function getTotalCodewords()
    {
        return $this->totalCodewords;
    }

    public function getDimensionForVersion()
    {
        return 17 + 4 * $this->versionNumber;
    }

    public function getEcBlocksForLevel(ErrorCorrectionLevel $ecLevel)
    {
        return $this->errorCorrectionBlocks[$ecLevel->getBits()];
    }

    public static function getProvisionalVersionForDimension($dimension)
    {
        if ($dimension % 4 !== 1) {
            throw new Exception\InvalidArgumentException('Dimension is not 1 mod 4');
        }

        return self::getVersionForNumber(($dimension - 17) >> 2);
    }

    public static function getVersionForNumber($versionNumber)
    {
        if ($versionNumber < 1 || $versionNumber > 40) {
            throw new Exception\InvalidArgumentException('Version number must be between 1 and 40');
        }

        if (!isset(self::$versions[$versionNumber])) {
            self::buildVersion($versionNumber);
        }

        return self::$versions[$versionNumber - 1];
    }

    public static function decodeVersionInformation($versionBits)
    {
        $bestDifference = PHP_INT_MAX;
        $bestVersion    = 0;

        foreach (self::$versionDecodeInfo as $i => $targetVersion) {
            if ($targetVersion === $versionBits) {
                return self::getVersionForNumber($i + 7);
            }

            $bitsDifference = FormatInformation::numBitsDiffering($versionBits, $targetVersion);

            if ($bitsDifference < $bestDifference) {
                $bestVersion    = $i + 7;
                $bestDifference = $bitsDifference;
            }
        }

        if ($bestDifference <= 3) {
            return self::getVersionForNumber($bestVersion);
        }

        return null;
    }

    public function buildFunctionPattern()
    {
        $dimension = $this->getDimensionForVersion();
        $bitMatrix = new BitMatrix($dimension);

        // Top left finder pattern + separator + format
        $bitMatrix->setRegion(0, 0, 9, 9);
        // Top right finder pattern + separator + format
        $bitMatrix->setRegion($dimension - 8, 0, 8, 9);
        // Bottom left finder pattern + separator + format
        $bitMatrix->setRegion(0, $dimension - 8, 9, 8);

        $max = count($this->alignmentPatternCenters);

        // Alignment patterns
        for ($x = 0; $x < $max; $x++) {
            $i = $this->alignmentPatternCenters[$x] - 2;

            for ($y = 0; $y < $max; $y++) {
                if (($x === 0 && ($y === 0 || $y === $max - 1)) || ($x === $max -1 && $y === 0)) {
                    // No alignment patterns near the three finder paterns
                    continue;
                }

                $bitMatrix->setRegion($this->alignmentPatternCenters[$y] - 2, $i, 5, 5);
            }
        }

        // Vertical timing pattern
        $bitMatrix->setRegion(6, 9, 1, $dimension - 17);
        // Horizontal timing pattern
        $bitMatrix->setRegion(9, 6, $dimension - 17, 1);

        if ($this->versionNumber > 6) {
            // Version info, top right
            $bitMatrix->setRegion($dimension - 11, 0, 3, 6);
            // Version info, bottom left
            $bitMatrix->setRegion(0, $dimension -11, 6, 3);
        }

        return $bitMatrix;
    }

    /**
     * Build and cache a specific version.
     *
     * See ISO 18004:2006 6.5.1 Table 9.
     *
     * @param  integer $versionNumber
     * @return void
     */
    protected static function buildVersion($versionNumber)
    {
        switch ($versionNumber) {
            case 1:
                $patterns = SplFixedArray::fromArray(array());
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(7, new EcBlock(1, 19)),
                    new EcBlocks(10, new EcBlock(1, 16)),
                    new EcBlocks(13, new EcBlock(1, 13)),
                    new EcBlocks(17, new EcBlock(1, 9)),
                ));
                break;

            case 2:
                $patterns = SplFixedArray::fromArray(array(6, 18));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(10, new EcBlock(1, 34)),
                    new EcBlocks(16, new EcBlock(1, 28)),
                    new EcBlocks(22, new EcBlock(1, 22)),
                    new EcBlocks(28, new EcBlock(1, 16)),
                ));
                break;

            case 3:
                $patterns = SplFixedArray::fromArray(array(6, 22));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(15, new EcBlock(1, 55)),
                    new EcBlocks(26, new EcBlock(1, 44)),
                    new EcBlocks(18, new EcBlock(2, 17)),
                    new EcBlocks(22, new EcBlock(2, 13)),
                ));
                break;

            case 4:
                $patterns = SplFixedArray::fromArray(array(6, 26));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(20, new EcBlock(1, 80)),
                    new EcBlocks(18, new EcBlock(2, 32)),
                    new EcBlocks(26, new EcBlock(3, 24)),
                    new EcBlocks(16, new EcBlock(4, 9)),
                ));
                break;

            case 5:
                $patterns = SplFixedArray::fromArray(array(6, 30));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(26, new EcBlock(1, 108)),
                    new EcBlocks(24, new EcBlock(2, 43)),
                    new EcBlocks(18, new EcBlock(2, 15), new EcBlock(2, 16)),
                    new EcBlocks(22, new EcBlock(2, 11), new EcBlock(2, 12)),
                ));
                break;

            case 6:
                $patterns = SplFixedArray::fromArray(array(6, 34));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(18, new EcBlock(2, 68)),
                    new EcBlocks(16, new EcBlock(4, 27)),
                    new EcBlocks(24, new EcBlock(4, 19)),
                    new EcBlocks(28, new EcBlock(4, 15)),
                ));
                break;

            case 7:
                $patterns = SplFixedArray::fromArray(array(6, 22, 38));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(20, new EcBlock(2, 78)),
                    new EcBlocks(18, new EcBlock(4, 31)),
                    new EcBlocks(18, new EcBlock(2, 14), new EcBlock(4, 15)),
                    new EcBlocks(26, new EcBlock(4, 13)),
                ));
                break;

            case 8:
                $patterns = SplFixedArray::fromArray(array(6, 24, 42));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(24, new EcBlock(2, 97)),
                    new EcBlocks(22, new EcBlock(2, 38), new EcBlock(2, 39)),
                    new EcBlocks(22, new EcBlock(4, 18), new EcBlock(2, 19)),
                    new EcBlocks(26, new EcBlock(4, 14), new EcBlock(2, 15)),
                ));
                break;

            case 9:
                $patterns = SplFixedArray::fromArray(array(6, 26, 46));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(30, new EcBlock(2, 116)),
                    new EcBlocks(22, new EcBlock(3, 36), new EcBlock(2, 37)),
                    new EcBlocks(20, new EcBlock(4, 16), new EcBlock(4, 17)),
                    new EcBlocks(24, new EcBlock(4, 12), new EcBlock(4, 13)),
                ));
                break;

            case 10:
                $patterns = SplFixedArray::fromArray(array(6, 28, 50));
                $ecBlocks = SplFixedArray::fromArray(array(
                    new EcBlocks(18, new EcBlock(2, 68), new EcBlock(2, 69)),
                    new EcBlocks(26, new EcBlock(4, 43), new EcBlock(1, 44)),
                    new EcBlocks(24, new EcBlock(6, 19), new EcBlock(2, 20)),
                    new EcBlocks(28, new EcBlock(6, 15), new EcBlock(2, 16)),
                ));
                break;

            // @TODO Port Version 11 to 40
        }

        self::$versions[$versionNumber - 1] = new self(
            $versionNumber,
            $patterns,
            $ecBlocks
        );
    }
}
