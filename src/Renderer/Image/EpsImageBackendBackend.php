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

final class EpsImageBackendBackend implements ImageBackendInterface
{
    /**
     * @var string
     */
    private $eps;

    public function __construct(int $size, ColorInterface $backgroundColor)
    {
        $this->eps = "%!PS-Adobe-3.0 EPSF-3.0\n"
            . "%%Creator: BaconQrCode\n"
            . sprintf("%%%%BoundingBox: 0 0 %d %d \n", $size, $size)
            . "1 -1 scale\n"
            . sprintf("0 -%d translate\n", $size);
    }

    public function scale(float $size) : void
    {
        $this->eps .= sprintf("%s %s scale\n", (string) $size, (string) $size);
    }

    public function translate(float $x, float $y) : void
    {
        $this->eps .= sprintf("%s %s translate\n", (string) $x, (string) $y);
    }

    public function rotate(int $degrees) : void
    {
        $this->eps .= sprintf("%d rotate\n", $degrees);
    }

    public function push() : void
    {
        $this->eps .= "gsave\n";
    }

    public function pop() : void
    {
        $this->eps .= "grestore\n";
    }

    public function drawPath(Path $path, ColorInterface $color) : void
    {
        if ($color instanceof Alpha && 0 === $color->getAlpha()) {
            return;
        }

        $this->eps .= "newpath\n";
        $this->drawPathOperations($path);
        $this->eps .= $this->getColorString($color) . "fill\n";
    }

    public function getBlob() : string
    {
        return $this->eps;
    }

    /**
     * @return float[]
     */
    private function drawPathOperations(Iterable $ops) : ?array
    {
        $fromX = null;
        $fromY = null;

        foreach ($ops as $op) {
            switch (true) {
                case $op instanceof Move:
                    $this->eps .= sprintf("%s %s moveto\n", (string) $op->getX(), (string) $op->getY());
                    $fromX = $op->getX();
                    $fromY = $op->getY();
                    break;

                case $op instanceof Line:
                    $this->eps .= sprintf("%s %s lineto\n", (string) $op->getX(), (string) $op->getY());
                    $fromX = $op->getX();
                    $fromY = $op->getY();
                    break;

                case $op instanceof EllipticArc:
                    $newFrom = $this->drawPathOperations($op->toCurves($fromX, $fromY));

                    if (null !== $newFrom) {
                        list($fromX, $fromY) = $newFrom;
                    }
                    break;

                case $op instanceof Curve:
                    $this->eps .= sprintf(
                        "%s %s %s %s %s %s curveto\n",
                        (string) $op->getX1(),
                        (string) $op->getY1(),
                        (string) $op->getX2(),
                        (string) $op->getY2(),
                        (string) $op->getX3(),
                        (string) $op->getY3()
                    );
                    $fromX = $op->getX3();
                    $fromY = $op->getY3();
                    break;

                case $op instanceof Close:
                    $this->eps .= "closepath\n";
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        if (null !== $fromX && null !== $fromY) {
            return [$fromX, $fromY];
        }

        return null;
    }

    private function getColorString(ColorInterface $color) : string
    {
        if ($color instanceof Rgb) {
            return sprintf(
                "%s %s %s setrgbcolor\n",
                (string) ($color->getRed() / 255),
                (string) ($color->getGreen() / 255),
                (string) ($color->getBlue() / 255)
            );
        }

        if ($color instanceof Cmyk) {
            return sprintf(
                "%s %s %s %s setcmykcolor\n",
                (string) ($color->getCyan() / 100),
                (string) ($color->getMagenta() / 100),
                (string) ($color->getYellow() / 100),
                (string) ($color->getBlack() / 100)
            );
        }

        if ($color instanceof Gray) {
            return sprintf(
                "%s setgraycolor\n",
                (string) ($color->getGray() / 100)
            );
        }

        return $this->getColorString($color->toCmyk());
    }
}
