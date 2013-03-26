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
 * Reed-Solomon en- and decoder.
 *
 * Based on libfec by Phil Karn, KA9Q.
 */
class ReedSolomon
{
    /**
     * Symbol size in bits.
     *
     * @var integer
     */
    protected $symbolSize;

    /**
     * Block size in symbols.
     *
     * @var integer
     */
    protected $blockSize;

    /**
     * First root of RS code generator polynomial, index form.
     *
     * @var integer
     */
    protected $firstRoot;

    /**
     * Primitive element to generate polynomial roots, index form.
     *
     * @var integer
     */
    protected $primitive;

    /**
     * Prim-th root of 1, index form.
     *
     * @var integer
     */
    protected $iPrimitive;

    /**
     * RS code generator polynomial degree (number of roots).
     *
     * @var integer
     */
    protected $numRoots;

    /**
     * Padding bytes at front of shortened block.
     *
     * @var integer
     */
    protected $padding;

    /**
     * Log lookup table.
     *
     * @var SplFixedArray
     */
    protected $alphaTo;

    /**
     * Anti-Log lookup table.
     *
     * @var SplFixedArray
     */
    protected $indexOf;

    /**
     * Generator polynomial.
     *
     * @var SplFixedArray
     */
    protected $generatorPoly;

    /**
     * Creates a new reed solomon instance.
     *
     * @param  integer $symbolSize
     * @param  integer $gfPoly
     * @param  integer $firstRoot
     * @param  integer $primitive
     * @param  integer $numRoots
     * @param  integer $padding
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function __construct($symbolSize, $gfPoly, $firstRoot, $primitive, $numRoots, $padding)
    {
        if ($symbolSize < 0 || $symbolSize > 8) {
            throw new Exception\InvalidArgumentException('Symbol size must be between 0 and 8');
        }

        if ($firstRoot < 0 || $firstRoot >= (1 << $symbolSize)) {
            throw new Exception\InvalidArgumentException('First root must be between 0 and ' . (1 << $symbolSize));
        }

        if ($numRoots < 0 || $numRoots >= (1 << $symbolSize)) {
            throw new Exception\InvalidArgumentException('Num roots must be between 0 and ' . (1 << $symbolSize));
        }

        if ($padding < 0 || $padding >= ((1 << $symbolSize) - 1 - $numRoots)) {
            throw new Exception\InvalidArgumentException('Padding must be between 0 and ' . ((1 << $symbolSize) - 1 - $numRoots));
        }

        $this->symbolSize = $symbolSize;            // NN, A0
        $this->blockSize  = (1 << $symbolSize) - 1; // MM
        $this->padding    = $padding;
        $this->alphaTo    = SplFixedArray::fromArray(array_fill(0, $this->blockSize + 1, 0));
        $this->indexOf    = SplFixedArray::fromArray(array_fill(0, $this->blockSize + 1, 0));

        // Generate galous field lookup table
        $this->indexOf[0]                = $this->blockSize;
        $this->alphaTo[$this->blockSize] = 0;

        $sr = 1;

        for ($i = 0; $i < $this->blockSize; $i++) {
            $this->indexOf[$sr] = $i;
            $this->alphaTo[$i]  = $sr;

            $sr <<= 1;

            if ($sr & (1 << $symbolSize)) {
                $sr ^= $gfPoly;
            }

            $sr &= $this->blockSize;
        }

        if ($sr !== 1) {
            throw new Exception\RuntimeException('Field generator polynomial is not primitive');
        }

        // Form RS code generator polynomial from its roots
        $this->generatorPoly = SplFixedArray::fromArray(array_fill(0, $numRoots + 1, 0));
        $this->firstRoot     = $firstRoot;
        $this->primitive     = $primitive;
        $this->numRoots      = $numRoots;

        // Find prim-th root of 1, used in decoding
        for ($iPrimitive = 1; ($iPrimitive % $primitive) !== 0; $iPrimitive += $this->blockSize);
        $this->iPrimitive = intval($iPrimitive / $primitive);

        $this->generatorPoly[0] = 1;

        for ($i = 0, $root = $firstRoot * $primitive; $i < $numRoots; $i++, $root += $primitive) {
            $this->generatorPoly[$i + 1] = 1;

            for ($j = $i; $j > 0; $j--) {
                if ($this->generatorPoly[$j] !== 0) {
                    $this->generatorPoly[$j] = $this->generatorPoly[$j - 1] ^ $this->alphaTo[$this->modNn($this->indexOf[$this->generatorPoly[$j]] + $root)];
                } else {
                    $this->generatorPoly[$j] = $this->generatorPoly[$j - 1];
                }
            }

            $this->generatorPoly[$j] = $this->alphaTo[$this->modNn($this->indexOf[$this->generatorPoly[0]] + $root)];
        }

        // Convert generator poly to index form for quicker encoding
        for ($i = 0; $i <= $numRoots; $i++) {
            $this->generatorPoly[$i] = $this->indexOf[$this->generatorPoly[$i]];
        }
    }

    /**
     * Encodes data and writes result back into parity array.
     *
     * @param  SplFixedArray $data
     * @param  SplFixedArray $parity
     * @return void
     */
    public function encode(SplFixedArray $data, SplFixedArray $parity)
    {
        for ($i = 0; $i < $this->numRoots; $i++) {
            $parity[$i] = 0;
        }

        $iterations = $this->symbolSize - $this->numRoots - $this->padding;

        for ($i = 0; $i < $iterations; $i++) {
            $feedback = $this->indexOf[$data[$i] ^ $parity[0]];

            if ($feedback !== $this->symbolSize) {
                // Feedback term is non-zero
                $feedback = $this->modNn($this->symbolSize - $this->generatorPoly[$this->numRoots] + $feedback);

                for ($j = 1; $j < $this->numRoots; $j++) {
                    $parity[$j] = $parity[$j] ^ $this->alphaTo[$this->modNn($feedback + $this->generatorPoly[$this->numRoots - $j])];
                }
            }

            for ($i = 0; $i < $this->numRoots; $i++) {
                $parity[$i] = $parity[$i + 1];
            }

            if ($feedback !== $this->symbolSize) {
                $parity[$this->numRoots - 1] = $this->alphaTo[$this->modNn($feedback + $this->generatorPoly[0])];
            } else {
                $parity[$this->numRoots - 1] = 0;
            }
        }
    }

    /**
     * Computes $x % GF_SIZE, where GF_SIZE is 2**GF_BITS - 1, without a slow
     * divide.
     *
     * @param  itneger $x
     * @return integer
     */
    protected function modNn($x)
    {
        while ($x >= $this->blockSize) {
            $x -= $this->blockSize;
            $x  = ($x >> $this->symbolSize) + ($x & $this->blockSize);
        }

        return $x;
    }
}
