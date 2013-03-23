<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode;

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

    protected static $versions;

    protected $versionNumber;

    protected $alignmentPatternCenters;

    protected $errorCorrectionBlocks;

    protected $totalCodewords;

    protected function __construct(
        $versionNumber,
        array $alignmentPatternCenters,
        array $errorCorrectionBlocks
    ) {
        $this->versionNumber           = $versionNumber;
        $this->alignmentPatternCenters = $alignmentPatternCenters;
        $this->errorCorrectionBlocks   = $errorCorrectionBlocks;

        $totalCodewords           = 0;
        $errorCorrectionCodewords = $errorCorrectionBlocks[0]->getErrorCorrectionCodewordsPerBlock();

        foreach ($errorCorrectionBlocks[0]->getErrorCorrectionBlocks() as $errorCorrectionBlock) {
            $totalCodewords += $errorCorrectionBlock->getCount() * (
                $errorCorrectionBlock->getDataCodewords() + $errorCorrectionCodewords
            );
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

    public function getErrorCorrectionBlocksForLevel(ErrorCorrectionLevel $level)
    {
        return $this->errorCorrectionBlocks[$level->getBits()];
    }
}
