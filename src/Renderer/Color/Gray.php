<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Color;

use BaconQrCode\Exception;

final class Gray implements ColorInterface
{
    /**
     * @param int $gray the gray value between 0 (black) and 100 (white)
     */
    public function __construct(private readonly int $gray)
    {
        if ($gray < 0 || $gray > 100) {
            throw new Exception\InvalidArgumentException('Gray must be between 0 and 100');
        }
    }

    public function getGray() : int
    {
        return $this->gray;
    }

    public function toRgb() : Rgb
    {
        // use 255/100 instead of 2.55 to avoid floating-point precision loss (100 * 2.55 = 254.999...)
        $value = (int) ($this->gray * 255 / 100);

        return new Rgb($value, $value, $value);
    }

    public function toCmyk() : Cmyk
    {
        return new Cmyk(0, 0, 0, 100 - $this->gray);
    }

    public function toGray() : Gray
    {
        return $this;
    }
}
