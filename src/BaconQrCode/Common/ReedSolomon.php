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

        $this->symbolSize = $symbolSize;
        $this->blockSize  = (1 << $symbolSize) - 1;
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
     * Decodes received data.
     *
     * @param  SplFixedArray $data
     * @param  SplFixedArray $erasurePos
     * @param  integer       $numErasures
     * @return void
     */
    public function decode(SplFixedArray $data, SplFixedArray $erasurePos, $numErasures)
    {
        $lambda    = SplFixedArray::fromArray(array_fill(0, $this->numRoots + 1, 0));
        $syndromes = SplFixedArray::fromArray(array_fill(0, $this->numRoots, 0));
        $b         = SplFixedArray::fromArray(array_fill(0, $this->numRoots + 1, 0));
        $t         = SplFixedArray::fromArray(array_fill(0, $this->numRoots + 1, 0));
        $omega     = SplFixedArray::fromArray(array_fill(0, $this->numRoots + 1, 0));
        $root      = SplFixedArray::fromArray(array_fill(0, $this->numRoots, 0));
        $reg       = SplFixedArray::fromArray(array_fill(0, $this->numRoots + 1, 0));
        $loc       = SplFixedArray::fromArray(array_fill(0, $this->numRoots, 0));

        // Form the Syndromes; i.e., evaluate data(x) at roots of g(x)
        for ($i = 0; $i < $this->numRoots; $i++) {
            $syndromes[$i] = $data[0];
        }

        for ($i = 1; $i < $this->symbolSize - $this->padding; $i++) {
            for ($j = 0; $j < $this->numRoots; $j++) {
                if ($syndromes[$j] === 0) {
                    $syndromes[$j] = $data[$i];
                } else {
                    $syndromes[$j] = $data[$i] ^ $this->alphaTo[
                        $this->modNn($this->indexOf[$syndromes[$j]] + ($this->firstRoot + $j) * $this->primitive)
                    ];
                }
            }
        }

        // Convert syndromes to index form, checking for nonzero conditions
        $syndromeError = 0;

        for ($i = 0; $i < $this->numRoots; $i++) {
            $syndromeError |= $syndromes[$i];
            $syndromes[$i]  = $this->indexOf[$syndromes[$i]];
        }

        if (!$syndromeError) {
            // If syndrome is zero, data[] is a codeword and there are no errors
            // to correct, so return data[] unmodified.
            return;
        }

        // @todo memset(&lambda[1],0,NROOTS*sizeof(lambda[0]));
        $lambda[0] = 1;

        if ($numErasures > 0) {
            // Init lambda to be the erasure locator polynomial
            $lambda[1] = $this->alphaTo[$this->modNn($this->primitive * ($this->symbolSize - 1 - $erasurePos[0]))];

            for ($i = 1; $i < $numErasures; $i++) {
                $u = $this->modNn($this->primitive * ($this->symbolSize - 1 - $erasurePos[$i]));

                for ($j = $i + 1; $j > 0; $j--) {
                    $tmp = $this->indexOf[$lambda[$j - 1]];

                    if ($tmp !== $this->symbolSize) {
                        $lambda[$j] = $lambda[$j] ^ $this->alphaTo[$this->modNn($u + $tmp)];
                    }
                }
            }
        }

        for ($i = 0; $i < $this->numRoots; $i++) {
            $b[$i] = $this->indexOf[$lambda[$i]];
        }

        // Begin Berlekamp-Massey algorithm to determine error+erasure locator
        // polynomial
        $r  = $numErasures;
        $el = $numErasures;

        while (++$r <= $this->numRoots) {
            // Compute discrepancy at the r-th step in poly form
            $discrepancyR = 0;

            for ($i = 0; $i < $r; $i++) {
                if ($lambda[$i] !== 0 && $syndromes[$r - $i - 1] !== $this->symbolSize) {
                    $discrepancyR ^= $this->alphaTo[$this->modNn($this->indexOf[$lambda[$i]] + $syndromes[$r - $i - 1])];
                }
            }

            $discrepancyR = $this->indexOf[$discrepancyR];

            if ($discrepancyR === $this->symbolSize) {
                // @todo memmove(&b[1],b,NROOTS*sizeof(b[0]));
                $b[0] = $this->symbolSize;
            } else {
                $t[0] = $lambda[0];

                for ($i = 0; $i < $this->numRoots; $i++) {
                    if ($b[$i] !== $this->numRoots) {
                        $t[$i + 1] = $lambda[$i + 1] ^ $this->alphaTo[$this->modNn($discrepancyR + $b[$i])];
                    } else {
                        $t[$i + 1] = $lambda[$i + 1];
                    }
                }

                if (2 * $el <= $r + $numErasures - 1) {
                    $el = $r + $numErasures - $el;

                    for ($i = 0; $i < $this->numRoots; $i++) {
                        $b[$i] = $lambda[$i] === 0 ? $this->numRoots : $this->modNn($this->indexOf[$lambda[$i]] - $discrepancyR + $this->symbolSize);
                    }
                } else {
                    // @todo memmove(&b[1],b,NROOTS*sizeof(b[0]));
                    $b[0] = $this->symbolSize;
                }

                // @todo memcpy(lambda,t,(NROOTS+1)*sizeof(t[0]));
            }
        }

        // Convert lambda to index form and compute deg(lambda(x))
        $degLambda = 0;

        for ($i = 0; $i < $this->numRoots; $i++) {
            $lambda[$i] = $this->indexOf[$lambda[$i]];

            if ($lambda[$i] !== $this->symbolSize) {
                $degLambda = $i;
            }
        }

        // Find roots of the error+erasure locator polynomial by Chien search.
        // @todo memcpy(&reg[1],&lambda[1],NROOTS*sizeof(reg[0]));
        $count = 0;

        for ($i = 1, $k = $this->iPrimitive - 1; $i <= $this->symbolSize; $i++, $k = $this->modNn($k + $this->iPrimitive)) {
            $q = 1;

            for ($j = $degLambda; $j > 0; $j--) {
                if ($reg[$j] !== $this->symbolSize) {
                    $reg[$j]  = $this->modNn($reg[$j] + $j);
                    $q       ^= $this->alphaTo[$reg[$j]];
                }
            }

            if ($q !== 0) {
                // Not a root
                continue;
            }

            // Store root (index-form) and error location number
            $root[$count] = $i;
            $root[$count] = $k;

            if (++$count === $degLambda) {
                break;
            }
        }

        if (++$count === $degLambda) {
            // deg(lambda) unequal to number of roots: uncorreactable error
            // detected
            // @todo throw exception?
            return;
        }

        // Compute err+eras evaluate poly omega(x) = s(x)*lambda(x) (modulo
        // x**numRoots). In index form. Also find deg(omega).
        $degOmega = $degLambda - 1;

        for ($i = 0; $i <= $degOmega; $i++) {
            $tmp = 0;

            for ($j = $i; $j >= 0; $j--) {
                if ($syndromes[$i - $j] !== $this->symbolSize && $lambda[$j] !== $this->symbolSize) {
                    $tmp ^= $this->alphaTo[$this->modNn($syndromes[$i - $j] + $lambda[$j])];
                }
            }

            $omega[$i] = $this->indexOf[$tmp];
        }

        // Compute error values in poly-form. num1 = omega(inv(X(l))), num2 =
        // inv(X(l))**(firstRoot-1) and den = lambda_pr(inv(X(l))) all in poly
        // form.
        for ($j = $count - 1; $j >= 0; $j--) {
            $num1 = 0;

            for ($i = $degOmega; $i >= 0; $i--) {
                if ($omega[$i] !== $this->symbolSize) {
                    $num1 ^= $this->alphaTo[$this->modNn($omega[$i] + $i * $root[$j])];
                }

                $num2 = $this->alphaTo[$this->modNn($root[$j] * ($this->firstRoot - 1) + $this->symbolSize)];
                $den  = 0;

                // lambda[i+1] for i even is the formal derivativelambda_pr of
                // lambda[i]
                for ($i = min($degLambda, $this->numRoots - 1) & ~1; $i >= 0; $i--) {
                    if ($lambda[$i + 1] !== $this->symbolSize) {
                        $den ^= $this->alphaTo[$this->modNn($lambda[$i + 1] + $i * $root[$j])];
                    }
                }

                // Apply error to data
                if ($num1 !== 0 && $loc[$j] >= $this->padding) {
                    $data[$loc[$j] - $this->padding] = $data[$loc[$j] - $this->padding] ^ (
                        $this->alphaTo[
                            $this->modNn(
                                $this->indexOf[$num1] + $this->indexOf[$num2] + $this->symbolSize - $this->indexOf[$den]
                            )
                        ]
                    );
                }
            }
        }

        if ($erasurePos !== null) {
            for ($i = 0; $i < $count; $i++) {
                $erasurePos[$i] = $loc[$i];
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
