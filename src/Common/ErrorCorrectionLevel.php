<?php
declare(strict_types = 1);

namespace BaconQrCode\Common;

use BaconQrCode\Exception\OutOfBoundsException;

/**
 * Enum representing the four error correction levels.
 */
enum ErrorCorrectionLevel
{
    /** ~7% correction */
    case L;
    /** ~15% correction */
    case M;
    /** ~25% correction */
    case Q;
    /** ~30% correction */
    case H;

    /**
     * @throws OutOfBoundsException if number of bits is invalid
     */
    public static function forBits(int $bits) : self
    {
        return match ($bits) {
            0 => self::M,
            1 => self::L,
            2 => self::H,
            3 => self::Q,
            default => throw new OutOfBoundsException('Invalid number of bits'),
        };
    }

    /**
     * Returns the two bits used to encode this error correction level.
     */
    public function getBits() : int
    {
        return match ($this) {
            self::L => 0x01,
            self::M => 0x00,
            self::Q => 0x03,
            self::H => 0x02,
        };
    }

    /**
     * Returns the ordinal index of this error correction level.
     *
     * The order matches the order in which EC blocks are stored in Version: L=0, M=1, Q=2, H=3.
     */
    public function ordinal() : int
    {
        return match ($this) {
            self::L => 0,
            self::M => 1,
            self::Q => 2,
            self::H => 3,
        };
    }
}
