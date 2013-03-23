<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\ReedSolomon;

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
        $this->cachedGenerators[] = new GenericGfPoly($field);
    }

    /**
     * Encode a value.
     *
     * @param  string  $toEncode
     * @param  integer $ecBytes
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function encode(array $toEncode, $ecBytes)
    {
        $toEncode = unpack('C*', $toEncode);

        if ($ecBytes === 0) {
            throw new Exception\InvalidArgumentException('No error correction bytes provided');
        }

        $dataBytes = strlen($toEncode) - $ecBytes;

        if ($dataBytes <= 0) {
            throw new Exception\InvalidArgumentException('No data bytes provided');
        }

        $generator        = $this->buildGenerator($ecBytes);
        $infoCoefficients = SplFixedArray::fromArray(array_splice($toEncode, 0, $dataBytes));

        $info      = new GenericGfPoly($this->field, $infoCoefficients);
        $info      = $info->multiplyByMonomial($ecBytes, 1);
        $remainder = $info->divide($generator);
        $remainder = $remainder[1];

        $coefficients        = $remainder->getCoefficients();
        $numZeroCoefficients = $ecBytes - count($coefficients);

        for ($i = 0; $i < $numZeroCoefficients; $i++) {
            $toEncode[$dataBytes + $i] = 0;
        }

        foreach ($coefficients as $key => $value) {
            $toEncode[$dataBytes + $numZeroCoefficients + $key] = $value;
        }

        return pack('C*', $toEncode);
    }

    protected function buildGenerator($degree)
    {
        if ($degree >= count($this->cachedGenerators)) {
            $lastGenerator = end($this->cachedGenerators);

            for ($d = count($this->cachedGenerators); $d <= $degree; $d++) {
                $nextGenerator = $lastGenerator->multiply(
                    new GenericGfPoly($field)
                );

                $this->cachedGenerators[] = $nextGenerator;
                $lastGenerator            = $nextGenerator;
            }
        }

        return $this->cachedGenerators[$degree];
    }
}
