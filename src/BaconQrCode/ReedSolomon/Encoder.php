<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\ReedSolomon;

use BaconQrCode\Common\ArrayUtils;
use SplFixedArray;

/**
 * Reed-Solomon encoder.
 */
class Encoder
{
    /**
     * Generic Galois Field.
     *
     * @var GenericGf
     */
    protected $field;

    /**
     * Cached generators.
     *
     * @var array
     */
    protected $cachedGenerators = array();

    /**
     * Create a new Reed-Solomon encoder.
     *
     * @param GenericGf $field
     */
    public function __construct(GenericGf $field)
    {
        $this->field = $field;
        $this->cachedGenerators[] = new GenericGfPoly($field, SplFixedArray::fromArray(array(1)));
    }

    /**
     * Encode a message.
     *
     * @param  SplFixedArray $toEncode
     * @param  integer       $ecBytes
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function encode(SplFixedArray $toEncode, $ecBytes)
    {
        if ($ecBytes === 0) {
            throw new Exception\InvalidArgumentException('No error correction bytes provided');
        }

        $dataBytes = count($toEncode) - $ecBytes;

        if ($dataBytes <= 0) {
            throw new Exception\InvalidArgumentException('No data bytes provided');
        }

        $generator        = $this->buildGenerator($ecBytes);
        $infoCoefficients = new SplFixedArray($dataBytes);
        ArrayUtils::arrayCopy($toEncode, 0, $infoCoefficients, 0, $dataBytes);

        $info              = new GenericGfPoly($this->field, $infoCoefficients);
        $info              = $info->multiplyByMonomial($ecBytes, 1);
        list(, $remainder) = $info->divide($generator);

        $coefficients        = $remainder->getCoefficients();
        $numZeroCoefficients = $ecBytes - count($coefficients);

        for ($i = 0; $i < $numZeroCoefficients; $i++) {
            $toEncode[$dataBytes + $i] = 0;
        }

        ArrayUtils::arrayCopy($coefficients, 0, $toEncode, $dataBytes + $numZeroCoefficients, count($coefficients));
    }

    protected function buildGenerator($degree)
    {
        if ($degree >= count($this->cachedGenerators)) {
            $lastGenerator = end($this->cachedGenerators);

            for ($d = count($this->cachedGenerators); $d <= $degree; $d++) {
                $nextGenerator = $lastGenerator->multiply(
                    new GenericGfPoly(
                        $this->field,
                        SplFixedArray::fromArray(
                            array(
                                1,
                                $this->field->exp($d - 1 + $this->field->getGeneratorBase())
                            )
                        )
                    )
                );

                $this->cachedGenerators[] = $nextGenerator;
                $lastGenerator            = $nextGenerator;
            }
        }

        return $this->cachedGenerators[$degree];
    }
}
