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
 * Represents a polynomial whose coefficients are elements of a GF.
 */
class GenericGfPoly
{
    protected $field;
    protected $coefficients;

    public function __construct(GenericGf $field, SplFixedArray $coefficients)
    {
        $coefficientsLength = count($coefficients);

        if ($coefficientsLength === 0) {
            throw new Exception\InvalidArgumentException('There must be at least one coefficient');
        }

        $this->field = $field;

        if ($coefficientsLength > 1 && $coefficients[0] === 0) {
            $firstNonZero = 1;

            while ($firstNonZero < $coefficientsLength && $coefficients[$firstNonZero] === 0) {
                $firstNonZero++;
            }

            if ($firstNonZero === $coefficientsLength) {
                $this->coefficients = $field->getZero()->getCoefficients();
            } else {
                $this->coefficients = new SplFixedArray($coefficientsLength - $firstNonZero);

                for ($i = $firstNonZero; $i < $coefficientsLength; $i++) {
                    $this->coefficients[] = $coefficients[$i];
                }
            }
        } else {
            $this->coefficients = $coefficients;
        }
    }

    public function getCoefficients()
    {
        return $this->coefficients;
    }

    public function getDegree()
    {
        return count($this->coefficients) - 1;
    }

    public function isZero()
    {
        return $this->coefficients[0] === 0;
    }

    public function getCoefficient($degree)
    {
        return $this->coefficients[count($this->coefficients) - 1 - $degree];
    }

    public function evaluateAt($a)
    {
        if ($a === 0) {
            return $this->getCoefficient(0);
        }

        if ($a === 1) {
            $result = 0;

            foreach ($this->coefficients as $coefficient) {
                $result = GenericGf::addOrSubstract($result, $coefficient);
            }

            return $result;
        }

        $size   = count($this->coefficients);
        $result = $coefficient[0];

        for ($i = 1; $i < $size; $i++) {
            $result = GenericGf::addOrSubstract($this->field->multiply($a, $result), $coefficient[$i]);
        }

        return $result;
    }

    public function addOrSubstract(GenericGfPoly $other)
    {
        if ($this->field !== $other->getField()) {
            throw new Exception\InvalidArgumentException('GenericGfPolys do not have same GenericGf field');
        }

        if ($this->isZero()) {
            return $other;
        } elseif ($other->isZero()) {
            return $this;
        }

        $smallerCoefficients = $this->coefficients;
        $largerCoefficients  = $other->getCoefficients();

        if (count($smallerCoefficients) > count($largerCoefficients)) {
            $temp                = $smallerCoefficients;
            $smallerCoefficients = $largerCoefficients;
            $largerCoefficients  = $temp;
        }

        $smallerCoefficientsLength = count($smallerCoefficients);
        $largerCoefficientsLength  = count($largerCoefficients);

        $sumDiff    = new SplFixedArray($largerCoefficientsLength);
        $lengthDiff = $largerCoefficientsLength - $smallerCoefficientsLength;

        for ($i = 0; $i < $lengthDiff; $i++) {
            $sumDiff[] = $largerCoefficients[$i];
        }

        for ($i = $lengthDiff; $i < $largerCoefficientsLength; $i++) {
            $sumDiff[$i] = GenericGf::addOrSubstract($smallerCoefficients[$i - $lengthDiff], $largerCoefficients[$i]);
        }

        return new GenericGfPoly($this->field, $sumDiff);
    }

    public function multiply($other)
    {
        if (is_int($other)) {
            if ($other === 0) {
                return $this->field->getZero();
            } elseif ($other === 1) {
                return $this;
            }

            $size    = count($this->coefficients);
            $product = new SplFixedArray($size);

            for ($i = 0; $i < $size; $i++) {
                $product[$i] = $this->field->multiply($this->coefficients[$i], $other);
            }
        } elseif ($other instanceof GenericGfPoly) {
            if ($this->field !== $other->getField()) {
                throw new Exception\InvalidArgumentException('GenericGfPolys do not have same GenericGf field');
            }

            if ($this->isZero() || $other->isZero()) {
                return $this->field->getZero();
            }

            $aCoefficients = $this->coefficients;
            $aLength       = count($aCoefficients);
            $bCoefficients = $other->getCoefficients();
            $bLength       = count($bCoefficients);
            $product       = new SplFixedArray($aLength + $bLength - 1);

            for ($i = 0; $i < $aLength; $i++) {
                $aCoefficient = $aCoefficients[$i];

                for ($j = 0; $j < $bLength; $j++) {
                    $product[$i + $j] = GenericGf::addOrSubstract(
                        $product[$i + $j],
                        $this->field->multiply($aCoefficient, $bCoefficients[$j])
                    );
                }
            }
        } else {
            throw Exception\InvalidArgumentException('Other must eithe be an integer or a GenericGfPoly');
        }

        return new GenericGfPoly($this->field, $product);
    }

    public function multiplyByMonomial($degree, $coefficient)
    {
        if ($degree < 0) {
            throw new Exception\InvalidArgumentException('Degree must be greater or equal to zero');
        } elseif ($coefficient === 0) {
            return $this->getField()->getZero();
        }

        $size    = count($this->coefficients);
        $product = new SplFixedArray($size + $degree);

        for ($i = 0; $i < $size; $i++) {
            $product[$i] = $this->field->multiply($this->coefficients[$i], $coefficient);
        }

        return new GenericGfPoly($this->field, $product);
    }

    public function divide(GenericGfPoly $other)
    {
        if ($this->field !== $other->getField()) {
            throw new Exception\InvalidArgumentException('GenericGfPolys do not have same GenericGf field');
        } elseif ($other->isZero()) {
            throw new Exception\InvalidArgumentException('Divide by zero');
        }

        $quotient  = $this->field->getZero();
        $remainder = $this;

        $denominatorLeadingTerm        = $other->getCoefficient($other->getDegree());
        $inverseDenominatorLeadingTerm = $this->field->inverse($denominatorLeadingTerm);

        while ($remainder->getDegree() >= $other->getDegree() && !$remainder->isZero()) {
            $degreeDifference  = $remainder->getDegree() - $other->getDegree();
            $scale             = $this->field->multiply($remainder->getCoefficient($remainder->getDegree()), $inverseDenominatorLeadingTerm);
            $term              = $other->multiplyByMonomial($degreeDifference, $scale);
            $iterationQuotient = $this->field->buildMonomial($degreeDifference, $scale);
            $quotient          = $quotient->addOrSubstract($iterationQuotient);
            $remainder         = $remainder->addOrSubstract($term);
        }

        return array($quotient, $remainder);
    }

    public function getField()
    {
        return $this->field;
    }
}
