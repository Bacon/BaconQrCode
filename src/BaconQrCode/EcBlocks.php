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
 * Encapsulates a set of error-correction blocks in one symbol version. Most
 * versions will use blocks of differing sizes within one version, so, this
 * encapsulates the parameters for each set of blocks. It also holds the number
 * of error-correction codewords per block since it will be the same across all
 * blocks within one version.
 */
class EcBlocks
{
    protected $ecCodewordsPerBlock;
    protected $ecBlocks;

    public function __construct($ecCodewordsPerBlock, EcBlock $ecb1, EcBlock $ecb2 = null)
    {
        $this->ecCodewordsPerBlock = $ecCodewordsPerBlock;

        $this->ecBlocks = new SplFixedArray($ecb2 === null ? 1 : 2);
        $this->ecBlocks[0] = $ecb1;

        if ($ecb2 !== null) {
            $this->ecBlocks[1] = $ecb2;
        }
    }

    public function getEcCodewordsPerBlock()
    {
        return $this->ecCodewordsPerBlock;
    }

    public function getNumBlocks()
    {
        $total = 0;

        foreach ($this->ecBlocks as $ecBlock) {
            $total += $ecBlock->getCount();
        }

        return $total;
    }

    public function getTotalEcCodewords()
    {
        return $this->ecCodewordsPerBlock * $this->getNumBlocks();
    }

    public function getEcBlocks()
    {
        return $this->ecBlocks;
    }
}
