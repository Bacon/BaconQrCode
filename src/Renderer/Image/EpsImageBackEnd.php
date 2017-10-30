<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;

final class EpsImageBackEnd implements ImageBackEndInterface
{
    private const PRECISION = 3;

    /**
     * @var string|null
     */
    private $eps;

    public function new(int $size, ColorInterface $backgroundColor) : void
    {
        $this->eps = "%!PS-Adobe-3.0 EPSF-3.0\n"
            . "%%Creator: BaconQrCode\n"
            . sprintf("%%%%BoundingBox: 0 0 %d %d \n", $size, $size)
            . "%%BeginProlog\n"
            . "save\n"
            . "50 dict begin\n"
            . "/q { gsave } bind def\n"
            . "/Q { grestore } bind def\n"
            . "/s { scale } bind def\n"
            . "/t { translate } bind def\n"
            . "/r { rotate } bind def\n"
            . "/n { newpath } bind def\n"
            . "/m { moveto } bind def\n"
            . "/l { lineto } bind def\n"
            . "/c { curveto } bind def\n"
            . "/z { closepath } bind def\n"
            . "/f { fill } bind def\n"
            . "/rgb { setrgbcolor } bind def\n"
            . "/cmyk { setcmykcolor } bind def\n"
            . "/gray { setgraycolor } bind def\n"
            . "%%EndProlog\n"
            . "1 -1 s\n"
            . sprintf("0 -%d t\n", $size);

        if ($backgroundColor instanceof Alpha && 0 === $backgroundColor->getAlpha()) {
            return;
        }

        $this->eps .= wordwrap(
            "0 0 m"
            . sprintf(" %s 0 l", (string) $size)
            . sprintf(" %s %s l", (string) $size, (string) $size)
            . sprintf(" 0 %s l", (string) $size)
            . " z"
            . ' ' .$this->getColorString($backgroundColor) . " f\n",
            75,
            "\n "
        );
    }

    public function scale(float $size) : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%1\$s %1\$s s\n", round($size, self::PRECISION));
    }

    public function translate(float $x, float $y) : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%s %s t\n", round($x, self::PRECISION), round($y, self::PRECISION));
    }

    public function rotate(int $degrees) : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= sprintf("%d r\n", $degrees);
    }

    public function push() : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "q\n";
    }

    public function pop() : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "Q\n";
    }

    public function drawPath(Path $path, ColorInterface $color) : void
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        if ($color instanceof Alpha && 0 === $color->getAlpha()) {
            return;
        }

        $fromX = 0;
        $fromY = 0;
        $this->eps .= wordwrap(
            "n "
            . $this->drawPathOperations($path, $fromX, $fromY)
            . ' ' . $this->getColorString($color) . " f\n",
            75,
            "\n "
        );
    }

    public function done() : string
    {
        if (null === $this->eps) {
            throw new RuntimeException('No image has been started');
        }

        $this->eps .= "%%TRAILER\nend restore\n%%EOF";
        $blob = $this->eps;
        $this->eps = null;

        return $blob;
    }

    private function drawPathOperations(Iterable $ops, &$fromX, &$fromY) : string
    {
        $pathData = [];

        foreach ($ops as $op) {
            switch (true) {
                case $op instanceof Move:
                    $fromX = $toX = round($op->getX(), self::PRECISION);
                    $fromY = $toY = round($op->getY(), self::PRECISION);
                    $pathData[] = sprintf("%s %s m", $toX, $toY);
                    break;

                case $op instanceof Line:
                    $fromX = $toX = round($op->getX(), self::PRECISION);
                    $fromY = $toY = round($op->getY(), self::PRECISION);
                    $pathData[] = sprintf("%s %s l", $toX, $toY);
                    break;

                case $op instanceof EllipticArc:
                    $pathData[] = $this->drawPathOperations($op->toCurves($fromX, $fromY), $fromX, $fromY);
                    break;

                case $op instanceof Curve:
                    $x1 = round($op->getX1(), self::PRECISION);
                    $y1 = round($op->getY1(), self::PRECISION);
                    $x2 = round($op->getX2(), self::PRECISION);
                    $y2 = round($op->getY2(), self::PRECISION);
                    $fromX = $x3 = round($op->getX3(), self::PRECISION);
                    $fromY = $y3 = round($op->getY3(), self::PRECISION);
                    $pathData[] = sprintf("%s %s %s %s %s %s c", $x1, $y1, $x2, $y2, $x3, $y3);
                    break;

                case $op instanceof Close:
                    $pathData[] = "z";
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        return implode(' ', $pathData);
    }

    private function getColorString(ColorInterface $color) : string
    {
        if ($color instanceof Rgb) {
            return sprintf(
                "%s %s %s rgb",
                (string) ($color->getRed() / 255),
                (string) ($color->getGreen() / 255),
                (string) ($color->getBlue() / 255)
            );
        }

        if ($color instanceof Cmyk) {
            return sprintf(
                "%s %s %s %s cmyk",
                (string) ($color->getCyan() / 100),
                (string) ($color->getMagenta() / 100),
                (string) ($color->getYellow() / 100),
                (string) ($color->getBlack() / 100)
            );
        }

        if ($color instanceof Gray) {
            return sprintf(
                "%s gray",
                (string) ($color->getGray() / 100)
            );
        }

        return $this->getColorString($color->toCmyk());
    }
}
