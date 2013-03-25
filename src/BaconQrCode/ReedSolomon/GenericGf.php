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
 * This class contains utility methods for performing mathematical operations
 * over the Galois Fields. Operations use a given primitive polynomial in
 * calculations.
 */
class GenericGf
{
    const INITIALIZATION_THRESHOLD = 0;

    protected static $defaultGenericGfs = array(
        'aztec_data_12'         => array(0x1069, 4096, 1),
        'aztec_data_10'         => array(0x409, 1024, 1),
        'aztec_data_8'          => 'data_matrix_field_256',
        'aztec_data_6'          => array(0x43, 64, 1),
        'aztec_param'           => array(0x13, 16, 1),
        'qr_code_field_256'     => array(0x011d, 256, 0),
        'data_matrix_field_256' => array(0x012d, 256, 1),
        'maxicode_field_64'     => 'aztec_data_6'
    );

    protected $expTable;
    protected $logTable;
    protected $one;
    protected $two;
    protected $size;
    protected $primitive;
    protected $generatorBase;
    protected $initialized = false;

    public function __construct($primitive, $size, $b)
    {
        $this->primitive     = $primitive;
        $this->size          = $size;
        $this->generatorBase = $b;

        if ($size <= self::INITIALIZATION_THRESHOLD) {
            $this->initialize();
        }
    }

    public static function getDefaultGenericGf($name)
    {
        if (!isset(self::$defaultGenericGfs[$name])) {
            throw new Exception\InvalidArgumentException('There is no generic GF with the name ' . $name);
        } elseif (!self::$defaultGenericGfs[$name] instanceof GenericGf) {
            if (is_string(self::$defaultGenericGfs[$name])) {
                self::$defaultGenericGfs[$name] = self::getDefaultGenericGf(self::$defaultGenericGfs[$name]);
            } else {
                self::$defaultGenericGfs[$name] = new GenericGf(
                    self::$defaultGenericGfs[$name][0],
                    self::$defaultGenericGfs[$name][1],
                    self::$defaultGenericGfs[$name][2]
                );
            }
        }

        return self::$defaultGenericGfs[$name];
    }

    protected function initialize()
    {
        $this->expTable = new SplFixedArray($this->size);
        $this->logTable = new SplFixedArray($this->size);

        $x = 1;

        for ($i = 0; $i < $this->size; $i++) {
            $this->expTable[$i] = $x;

            $x <<= 1;

            if ($x >= $this->size) {
                $x ^= $this->primitive;
                $x &= $this->size - 1;
            }
        }

        for ($i = 0; $i < $this->size - 1; $i++) {
            $this->logTable[$this->expTable[$i]] = $i;
        }

        $this->zero = new GenericGfPoly($this, SplFixedArray::fromArray(array(0)));
        $this->one  = new GenericGfPoly($this, SplFixedArray::fromArray(array(1)));

        $this->initialized = true;
    }

    protected function checkInit()
    {
        if (!$this->initialized) {
            $this->initialize();
        }
    }

    public function getZero()
    {
        $this->checkInit();
        return $this->zero;
    }

    public function getOne()
    {
        $this->checkInit();
        return $this->one;
    }

    public function buildMonomial($degree, $coefficient)
    {
        $this->checkInit();

        if ($degree < 0) {
            throw new Exception\InvalidArgumentException('Degree must be equal or greater than zero');
        } elseif ($coefficient === 0) {
            return $this->zero;
        }

        $coefficients = new SplFixedArray($degree + 1);
        $coefficients[0] = $coefficient;

        return new GenericGfPoly($this, $coefficients);
    }

    public static function addOrSubtract($a, $b)
    {
        return $a ^ $b;
    }

    public function exp($a)
    {
        $this->checkInit();

        $a = (int) $a;

        return $this->expTable[$a];
    }

    public function log($a)
    {
        $this->checkInit();

        $a = (int) $a;

        if ($a === 0) {
            throw new Exception\InvalidArgumentException('Value may not be zero');
        }

        return $this->logTable[$a];
    }

    public function inverse($a)
    {
        $this->checkInit();

        $a = (int) $a;

        if ($a === 0) {
            throw new Exception\InvalidArgumentException('Value may not be zero');
        }

        return $this->expTable[$this->size - $this->logTable[$a] - 1];
    }

    public function multiply($a, $b)
    {
        $this->checkInit();

        $a = (int) $a;
        $b = (int) $b;

        if ($a === 0 || $b === 0) {
            return 0;
        }

        try {
        return $this->expTable[($this->logTable[$a] + $this->logTable[$b]) % ($this->size - 1)];
        } catch (\Exception $e) {
            var_dump($this->size, count($this->logTable), $a, $b);
            throw $e;
        }
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getGeneratorBase()
    {
        return $this->generatorBase;
    }
}
