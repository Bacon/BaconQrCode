<?php
declare(strict_types = 1);

namespace BaconQrCode\Common;

/**
 * Enum representing various modes in which data can be encoded to bits.
 */
enum Mode
{
    case TERMINATOR;
    case NUMERIC;
    case ALPHANUMERIC;
    case STRUCTURED_APPEND;
    case BYTE;
    case ECI;
    case KANJI;
    case FNC1_FIRST_POSITION;
    case FNC1_SECOND_POSITION;
    case HANZI;

    /**
     * Returns the number of bits used in a specific QR code version.
     */
    public function getCharacterCountBits(Version $version) : int
    {
        $number = $version->getVersionNumber();

        if ($number <= 9) {
            $offset = 0;
        } elseif ($number <= 26) {
            $offset = 1;
        } else {
            $offset = 2;
        }

        return match ($this) {
            self::TERMINATOR, self::STRUCTURED_APPEND, self::ECI,
            self::FNC1_FIRST_POSITION, self::FNC1_SECOND_POSITION => [0, 0, 0][$offset],
            self::NUMERIC => [10, 12, 14][$offset],
            self::ALPHANUMERIC => [9, 11, 13][$offset],
            self::BYTE => [8, 16, 16][$offset],
            self::KANJI, self::HANZI => [8, 10, 12][$offset],
        };
    }

    /**
     * Returns the four bits used to encode this mode.
     */
    public function getBits() : int
    {
        return match ($this) {
            self::TERMINATOR => 0x00,
            self::NUMERIC => 0x01,
            self::ALPHANUMERIC => 0x02,
            self::STRUCTURED_APPEND => 0x03,
            self::BYTE => 0x04,
            self::FNC1_FIRST_POSITION => 0x05,
            self::ECI => 0x07,
            self::KANJI => 0x08,
            self::FNC1_SECOND_POSITION => 0x09,
            self::HANZI => 0x0d,
        };
    }
}
