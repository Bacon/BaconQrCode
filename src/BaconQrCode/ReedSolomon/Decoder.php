<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\ReedSolomon;

use BaconQrCode\Exception;
use SplFixedArray;

/**
 * Reed-Solomon decoder.
 */
class Decoder
{
    /**
     * Generic Galois Field.
     *
     * @var GenericGf
     */
    protected $field;

    /**
     * Create a new decoder.
     *
     * @param GenericGf $field
     */
    public function __construct(GenericGf $field)
    {
        $this->field = $field;
    }

    /**
     * Decodes given set of received codewords, which include both data and
     * error-correction codewords.
     *
     * Really, this means it uses Reed-Solomon to detect and correct errors,
     * in-place, in the input.
     *
     * @param  SplFixedArray $received
     * @param  integer       $twoS
     * @return void
     * @throws Exception\RuntimeException
     */
    public function decode(SplFixedArray $received, $twoS)
    {
        $poly                 = new GenericGfPoly($this->field, $received);
        $syndromeCoefficients = new SplFixedArray($twoS);
        $noError              = true;

        for ($i = 0; $i < $twoS; $i++) {
            $eval = $poly->evaluateAt($this->field->exp($i + $this->field->getGeneratorBase()));
            $syndromeCoefficients[count($syndromeCoefficients) - 1 - $i] = $eval;

            if ($eval !== 0) {
                $noError = false;
            }
        }

        if ($noError) {
            return;
        }

        $syndrome   = new GenericGfPoly($this->field, $syndromeCoefficients);
        $sigmaOmega = $this->runEuclideanAlgorithm($this->field->buildMonomial($twoS, 1), $syndrome, $twoS);
        $sigma      = $sigmaOmega[0];
        $omega      = $sigmaOmega[1];

        $errorLocations  = $this->findErrorLocations($sigma);
        $errorMagnitudes = $this->findErrorMagnitudes($omega, $errorLocations);

        for ($i = 0; $i < count($errorLocations); $i++) {
            $position = count($received) - 1 - $this->field->log($errorLocations[$i]);

            if ($position < 0) {
                throw new Exception\RuntimeException('Bad error location');
            }

            $received[$position] = GenericGf::addOrSubtract($received[$position], $errorMagnitudes[$i]);
        }
    }

    /**
     * Runs the euclidean algorithm on two GF polys.
     *
     * @param  GenericGfPoly $a
     * @param  GenericGfPoly $b
     * @param  integer       $radius
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function runEuclideanAlgorithm(GenericGfPoly $a, GenericGfPoly $b, $radius)
    {
        if ($a->getDegree() < $b->getDegree()) {
            $temp = $a;
            $a    = $b;
            $b    = $temp;
        }

        $rLast = $a;
        $r     = $b;
        $tLast = $this->field->getZero();
        $t     = $this->field->getOne();

        while ($r->getDegree() >= $radius / 2) {
            $rLastLast = $rLast;
            $tLastLast = $tLast;
            $rLast     = $r;
            $tLast     = $t;

            if ($rLast->isZero()) {
                throw new Exception\RuntimeException('r_{$i-1} was zero');
            }

            $r = $rLastLast;
            $q = $this->field->getZero();

            $denominatorLeadingTerm = $rLast->getCoefficient($rLast->getDegree());
            $dltInverse             = $this->field->inverse($denominatorLeadingTerm);

            while ($r->getDegree() >= $rLast->getDegree() && !$r->isZero()) {
                $degreeDiff = $r->getDegree() - $rLast->getDegree();
                $scale      = $this->field->multiply($r->getCoefficient($r->getDegree()), $dltInverse);
                $q          = $q->addOrSubtract($this->field->buildMonomial($degreeDiff, $scale));
                $r          = $r->addOrSubtract($rLast->multiplyByMonomial($degreeDiff, $scale));
            }

            $t = $q->multiply($tLast)->addOrSubtract($tLastLast);
        }

        $sigmaTildeAtZero = $t->getCoefficient(0);

        if ($sigmaTildeAtZero === 0) {
            throw new Exception\RuntimeException('sigmaTilde(0) was zero');
        }

        $inverse = $this->field->inverse($sigmaTildeAtZero);
        $sigma   = $t->multiply($inverse);
        $omega   = $r->multiply($inverse);

        return array($sigma, $omega);
    }

    /**
     * Finds error locations in a GF poly.
     *
     * @param  GenericGfPoly $errorLocator
     * @return SplFixedArray
     * @throws Exception\RuntimeException
     */
    protected function findErrorLocations(GenericGfPoly $errorLocator)
    {
        $numErrors = $errorLocator->getDegree();

        if ($numErrors === 1) {
            return SplFixedArray::fromArray(array($errorLocator->getCoefficient(1)));
        }

        $result = new SplFixedArray($numErrors);
        $e      = 0;

        for ($i = 1; $i < $this->field->getSize() && $e < $numErrors; $i++) {
            if ($errorLocator->evaluateAt($i) === 0) {
                $result[$e] = $this->field->inverse($i);
                $e++;
            }
        }

        if ($e !== $numErrors) {
            throw new Exception\RuntimeException('Error locator degree does not match number of roots');
        }

        return $result;
    }

    /**
     * Finds error magnitudes in a GF poly.
     *
     * @param  GenericGfPoly $errorEvaluator
     * @param  SplFixedArray $errorLocations
     * @return SplFixedArray
     */
    protected function findErrorMagnitudes(GenericGfPoly $errorEvaluator, SplFixedArray $errorLocations)
    {
        $s      = count($errorLocations);
        $result = new SplFixedArray($s);

        for ($i = 0; $i < $s; $i++) {
            $xiInverse   = $this->field->inverse($errorLocations[$i]);
            $denominator = 1;

            for ($j = 0; $j < $s; $j++) {
                if ($i !== $j) {
                    $term        = $this->field->multiply($errorLocations[$j], $xiInverse);
                    $termPlus1   = ($term & 0x1) === 0 ? $term | 1 : $term & ~1;
                    $denominator = $this->field->multiply($denominator, $termPlus1);
                }
            }

            $result[$i] = $this->field->multiply(
                $errorEvaluator->evaluateAt($xiInverse),
                $this->field->inverse($denominator)
            );

            if ($this->field->getGeneratorBase() !== 0) {
                $result[$i] = $this->field->multiply($result[$i], $xiInverse);
            }
        }

        return $result;
    }
}
