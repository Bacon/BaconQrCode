<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Color;

use BaconQrCode\Exception;

final class Cmyk implements ColorInterface
{
    /**
     * @param int $cyan the cyan amount, 0 to 100
     * @param int $magenta the magenta amount, 0 to 100
     * @param int $yellow the yellow amount, 0 to 100
     * @param int $black the black amount, 0 to 100
     */
    public function __construct(
        private readonly int $cyan,
        private readonly int $magenta,
        private readonly int $yellow,
        private readonly int $black
    ) {
        if ($cyan < 0 || $cyan > 100) {
            throw new Exception\InvalidArgumentException('Cyan must be between 0 and 100');
        }

        if ($magenta < 0 || $magenta > 100) {
            throw new Exception\InvalidArgumentException('Magenta must be between 0 and 100');
        }

        if ($yellow < 0 || $yellow > 100) {
            throw new Exception\InvalidArgumentException('Yellow must be between 0 and 100');
        }

        if ($black < 0 || $black > 100) {
            throw new Exception\InvalidArgumentException('Black must be between 0 and 100');
        }
    }

    public function getCyan() : int
    {
        return $this->cyan;
    }

    public function getMagenta() : int
    {
        return $this->magenta;
    }

    public function getYellow() : int
    {
        return $this->yellow;
    }

    public function getBlack() : int
    {
        return $this->black;
    }

    public function toRgb() : Rgb
    {
        $c = $this->cyan / 100;
        $m = $this->magenta / 100;
        $y = $this->yellow / 100;
        $k = $this->black / 100;

        return new Rgb(
            (int) (255 * (1 - $c) * (1 - $k)),
            (int) (255 * (1 - $m) * (1 - $k)),
            (int) (255 * (1 - $y) * (1 - $k))
        );
    }

    public function toCmyk() : Cmyk
    {
        return $this;
    }

    public function toGray() : Gray
    {
        return $this->toRgb()->toGray();
    }
}
