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
 * A simple, fast array of bits.
 */
class BitArray
{
    protected $bits;
    protected $size;

    public function __construct($size = 0)
    {
        $this->size = $size;
        $this->bits = new SplFixedArray(($this->size + 31) >> 3);
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getSizeInBytes()
    {
        return ($this->size + 7) >> 3;
    }

    public function ensureCapacity($size)
    {
        if ($size > count($this->bits) << 5) {
            $this->bits->setSize(($size + 31) >> 5);
        }
    }

    public function get($i)
    {
        return ($this->bits[$i >> 5] & (1 << ($i & 0x1f))) !== 0;
    }

    public function set($i)
    {
        $this->bits[$i >> 5] = $this->bits[$i >> 5] | 1 << ($i & 0x1f);
    }

    public function flip($i)
    {
        $this->bits[$i >> 5] ^= 1 << ($i & 0x1f);
    }

    public function getNextSet($from)
    {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsOffset  = $from >> 5;
        $currentBits = $this->bits[$bitsOffset];
        $bitsLength  = count($this->bits);

        $currentBits &= ~((1 << ($from & 0x1f)) - 1);

        while ($currentBits === 0) {
            if (++$bitsOffset === $bitsLength) {
                return $this->size;
            }

            $currentBits = $this->bits[$bitsOffset];
        }

        $result = ($bitsOffset << 5) + $this->numberOfTrailingZeros($currentBits);

        return $result > $this->size ? $this->size : $result;
    }

    public function getNextUnset($from)
    {
        if ($from >= $this->size) {
            return $this->size;
        }

        $bitsOffset  = $from >> 5;
        $currentBits = ~$this->bits[$bitsOffset];
        $bitsLength  = count($this->bits);

        $currentBits &= ~((1 << ($from & 0x1f)) - 1);

        while ($currentBits === 0) {
            if (++$bitsOffset === $bitsLength) {
                return $this->size;
            }

            $currentBits = ~$this->bits[$bitsOffset];
        }

        $result = ($bitsOffset << 5) + $this->numberOfTrailingZeros($currentBits);

        return $result > $this->size ? $this->size : $result;
    }

    public function setBulk($i, $newBits)
    {
        $this->bits[$i >> 5] = $newBits;
    }

    public function setRange($start, $end)
    {
        if ($end < $start) {
            throw new Exception\InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return;
        }

        $end--;

        $firstInt = $start >> 5;
        $lastInt  = $end >> 5;

        for ($i = $firstInt; $i <= $lastInt; $i++) {
            $firstBit = $i > $firstInt ?  0 : $start & 0x1f;
            $lastBit  = $i < $lastInt ? 31 : $end & 0x1f;

            if ($firstBit === 0 && $lastBit === 31) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstBit; $j < $lastBit; $j++) {
                    $mask |= 1 << $j;
                }
            }

            $this->bits[$i] = $this->bits[$i] | $mask;
        }
    }

    public function clear()
    {
        $bitsLength = count($this->bits);

        for ($i = 0; $i < $bitsLength; $i++) {
            $this->bits[$i] = 0;
        }
    }

    public function isRange($start, $end, $value)
    {
        if ($end < $start) {
            throw new Exception\InvalidArgumentException('End must be greater or equal to start');
        }

        if ($end === $start) {
            return;
        }

        $end--;

        $firstInt = $start >> 5;
        $lastInt  = $end >> 5;

        for ($i = $firstInt; $i <= $lastInt; $i++) {
            $firstBit = $i > $firstInt ?  0 : $start & 0x1f;
            $lastBit  = $i < $lastInt ? 31 : $end & 0x1f;

            if ($firstBit === 0 && $lastBit === 31) {
                $mask = 0x7fffffff;
            } else {
                $mask = 0;

                for ($j = $firstBit; $j <= $lastBit; $j++) {
                    $mask |= 1 << $j;
                }
            }

            if (($this->bits[$i] & $mask) !== ($value ? $mask : 0)) {
                return false;
            }
        }

        return true;
    }

    public function appendBit($bit)
    {
        $this->ensureCapacity($this->size + 1);

        if ($bit) {
            $this->bits[$this->size >> 5] |= 1 << ($this->size & 0x1f);
        }

        $this->size++;
    }

    public function appendBits($value, $numBits)
    {
        if ($numBits < 0 || $numBits > 32) {
            throw new Exception\InvalidArgumentException('Num bits must be between 0 and 32');
        }

        $this->ensureCapacity($this->size + $numBits);

        for ($numBitsLeft = $numBits; $numBitsLeft > 0; $numBitsLeft--) {
            $this->appendBit((($value >> ($numBitsLeft - 1)) & 0x01) === 1);
        }
    }

    public function appendBitArray(BitArray $other)
    {
        $otherSize = $other->getSize();
        $this->ensureCapacity($this->size + $other->getSize());

        for ($i = 0; $i < $otherSize; $i++) {
            $this->appendBit($other->get($i));
        }
    }

    public function xorBits(BitArray $other)
    {
        $bitsLength = count($this->bits);
        $otherBits  = $other->getBitArray();

        if ($bitsLength !== count($otherBits)) {
            throw new Exception\InvalidArgumentException('Sizes don\'t match');
        }

        for ($i = 0; $i < $bitsLength; $i++) {
            $this->bits[$i] ^= $otherBits[$i];
        }
    }

    public function toBytes($bitOffset, $numBytes)
    {
        $bytes = new SplFixedArray($numBytes);

        for ($i = 0; $i < $numBytes; $i++) {
            $byte = 0;

            for ($j = 0; $j < 8; $j++) {
                if ($this->get($bitOffset)) {
                    $byte |= 1 << (7 - $j);
                }

                $bitOffset++;
            }

            $bytes[$i] = $byte;
        }

        return $bytes;
    }

    public function getBitArray()
    {
        return $this->bits;
    }

    public function reverse()
    {
        $newBits = new SplFixedArray(count($this->bits));

        for ($i = 0; $i < $this->size; $i++) {
            if ($this->get($this->size - $i - 1)) {
                $newBits[$i >> 5] |= 1 << ($i & 0x1f);
            }
        }

        $this->bits = newBits;
    }

    protected function numberOfTrailingZeros($i)
    {
        $lastPos = strrpos(str_pad(decbin($i), 32, '0', STR_PAD_LEFT), '1');

        return $lastPos === false ? 32 : 31 - $lastPos;
    }
}
