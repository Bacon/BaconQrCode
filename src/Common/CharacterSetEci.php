<?php
declare(strict_types = 1);

namespace BaconQrCode\Common;

use BaconQrCode\Exception\InvalidArgumentException;

/**
 * Encapsulates a Character Set ECI, according to "Extended Channel Interpretations" 5.3.1.1 of ISO 18004.
 */
enum CharacterSetEci
{
    case CP437;
    case ISO8859_1;
    case ISO8859_2;
    case ISO8859_3;
    case ISO8859_4;
    case ISO8859_5;
    case ISO8859_6;
    case ISO8859_7;
    case ISO8859_8;
    case ISO8859_9;
    case ISO8859_10;
    case ISO8859_11;
    case ISO8859_12;
    case ISO8859_13;
    case ISO8859_14;
    case ISO8859_15;
    case ISO8859_16;
    case SJIS;
    case CP1250;
    case CP1251;
    case CP1252;
    case CP1256;
    case UNICODE_BIG_UNMARKED;
    case UTF8;
    case ASCII;
    case BIG5;
    case GB18030;
    case EUC_KR;

    /**
     * Returns the ECI values for this character set.
     *
     * @return int[]
     */
    public function getValues() : array
    {
        return match ($this) {
            self::CP437 => [0, 2],
            self::ISO8859_1 => [1, 3],
            self::ISO8859_2 => [4],
            self::ISO8859_3 => [5],
            self::ISO8859_4 => [6],
            self::ISO8859_5 => [7],
            self::ISO8859_6 => [8],
            self::ISO8859_7 => [9],
            self::ISO8859_8 => [10],
            self::ISO8859_9 => [11],
            self::ISO8859_10 => [12],
            self::ISO8859_11 => [13],
            self::ISO8859_12 => [14],
            self::ISO8859_13 => [15],
            self::ISO8859_14 => [16],
            self::ISO8859_15 => [17],
            self::ISO8859_16 => [18],
            self::SJIS => [20],
            self::CP1250 => [21],
            self::CP1251 => [22],
            self::CP1252 => [23],
            self::CP1256 => [24],
            self::UNICODE_BIG_UNMARKED => [25],
            self::UTF8 => [26],
            self::ASCII => [27, 170],
            self::BIG5 => [28],
            self::GB18030 => [29],
            self::EUC_KR => [30],
        };
    }

    /**
     * Returns the primary value.
     */
    public function getValue() : int
    {
        return $this->getValues()[0];
    }

    /**
     * Returns the other encoding names for this character set.
     *
     * @return string[]
     */
    public function getOtherEncodingNames() : array
    {
        return match ($this) {
            self::ISO8859_1 => ['ISO-8859-1'],
            self::ISO8859_2 => ['ISO-8859-2'],
            self::ISO8859_3 => ['ISO-8859-3'],
            self::ISO8859_4 => ['ISO-8859-4'],
            self::ISO8859_5 => ['ISO-8859-5'],
            self::ISO8859_6 => ['ISO-8859-6'],
            self::ISO8859_7 => ['ISO-8859-7'],
            self::ISO8859_8 => ['ISO-8859-8'],
            self::ISO8859_9 => ['ISO-8859-9'],
            self::ISO8859_10 => ['ISO-8859-10'],
            self::ISO8859_11 => ['ISO-8859-11'],
            self::ISO8859_12 => ['ISO-8859-12'],
            self::ISO8859_13 => ['ISO-8859-13'],
            self::ISO8859_14 => ['ISO-8859-14'],
            self::ISO8859_15 => ['ISO-8859-15'],
            self::ISO8859_16 => ['ISO-8859-16'],
            self::SJIS => ['Shift_JIS'],
            self::CP1250 => ['windows-1250'],
            self::CP1251 => ['windows-1251'],
            self::CP1252 => ['windows-1252'],
            self::CP1256 => ['windows-1256'],
            self::UNICODE_BIG_UNMARKED => ['UTF-16BE', 'UnicodeBig'],
            self::UTF8 => ['UTF-8'],
            self::ASCII => ['US-ASCII'],
            self::GB18030 => ['GB2312', 'EUC_CN', 'GBK'],
            self::EUC_KR => ['EUC-KR'],
            default => [],
        };
    }

    /**
     * Gets character set ECI by value.
     *
     * Returns the representing ECI of a given value, or null if it is legal but unsupported.
     *
     * @throws InvalidArgumentException if value is not between 0 and 900
     */
    public static function getCharacterSetEciByValue(int $value) : ?self
    {
        if ($value < 0 || $value >= 900) {
            throw new InvalidArgumentException('Value must be between 0 and 900');
        }

        $valueToEci = self::valueToEci();

        if (! array_key_exists($value, $valueToEci)) {
            return null;
        }

        return $valueToEci[$value];
    }

    /**
     * Returns character set ECI by name.
     *
     * Returns the representing ECI of a given name, or null if it is legal but unsupported
     */
    public static function getCharacterSetEciByName(string $name) : ?self
    {
        $nameToEci = self::nameToEci();
        $name = strtolower($name);

        if (! array_key_exists($name, $nameToEci)) {
            return null;
        }

        return $nameToEci[$name];
    }

    private static function valueToEci() : array
    {
        static $cache = null;

        if (null !== $cache) {
            return $cache;
        }

        $cache = [];

        foreach (self::cases() as $eci) {
            foreach ($eci->getValues() as $value) {
                $cache[$value] = $eci;
            }
        }

        return $cache;
    }

    private static function nameToEci() : array
    {
        static $cache = null;

        if (null !== $cache) {
            return $cache;
        }

        $cache = [];

        foreach (self::cases() as $eci) {
            $cache[strtolower($eci->name)] = $eci;

            foreach ($eci->getOtherEncodingNames() as $name) {
                $cache[strtolower($name)] = $eci;
            }
        }

        return $cache;
    }
}
