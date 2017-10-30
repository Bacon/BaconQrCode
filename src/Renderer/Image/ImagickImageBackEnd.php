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
use Imagick;
use ImagickDraw;
use ImagickPixel;

final class ImagickImageBackEnd implements ImageBackEndInterface
{
    /**
     * @var string
     */
    private $imageFormat;

    /**
     * @var int
     */
    private $compressionQuality;

    /**
     * @var Imagick|null
     */
    private $image;

    /**
     * @var ImagickDraw|null
     */
    private $draw;

    public function __construct(string $imageFormat = 'png', int $compressionQuality = 100)
    {
        $this->imageFormat = $imageFormat;
        $this->compressionQuality = $compressionQuality;
    }

    public function new(int $size, ColorInterface $backgroundColor) : void
    {
        $this->image = new Imagick();
        $this->image->newImage($size, $size, $this->getColorPixel($backgroundColor));
        $this->image->setImageFormat($this->imageFormat);
        $this->image->setCompressionQuality($this->compressionQuality);
        $this->draw = new ImagickDraw();
    }

    public function scale(float $size) : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->scale($size, $size);
    }

    public function translate(float $x, float $y) : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->translate($x, $y);
    }

    public function rotate(int $degrees) : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->rotate($degrees);
    }

    public function push() : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->push();
    }

    public function pop() : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->pop();
    }

    public function drawPath(Path $path, ColorInterface $color) : void
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->draw->setFillColor($this->getColorPixel($color));
        $this->draw->pathStart();

        foreach ($path as $op) {
            switch (true) {
                case $op instanceof Move:
                    $this->draw->pathMoveToAbsolute($op->getX(), $op->getY());
                    break;

                case $op instanceof Line:

                    $this->draw->pathLineToAbsolute($op->getX(), $op->getY());
                    break;

                case $op instanceof EllipticArc:
                    $this->draw->pathEllipticArcAbsolute(
                        $op->getXRadius(),
                        $op->getYRadius(),
                        $op->getXAxisAngle(),
                        $op->isLargeArc(),
                        $op->isSweep(),
                        $op->getX(),
                        $op->getY()
                    );
                    break;

                case $op instanceof Curve:
                    $this->draw->pathCurveToAbsolute(
                        $op->getX1(),
                        $op->getY1(),
                        $op->getX2(),
                        $op->getY2(),
                        $op->getX3(),
                        $op->getY3()
                    );
                    break;

                case $op instanceof Close:
                    $this->draw->pathClose();
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        $this->draw->pathFinish();
    }

    public function done() : string
    {
        if (null === $this->draw) {
            throw new RuntimeException('No image has been started');
        }

        $this->image->drawImage($this->draw);

        $blob = $this->image->getImageBlob();
        $this->draw->clear();
        $this->image->clear();
        $this->draw = null;
        $this->image = null;

        return $blob;
    }

    private function getColorPixel(ColorInterface $color) : ImagickPixel
    {
        $alpha = 100;

        if ($color instanceof Alpha) {
            $alpha = $color->getAlpha();
            $color = $color->getBaseColor();
        }

        if ($color instanceof Rgb) {
            return new ImagickPixel(sprintf(
                'rgba(%d, %d, %d, %F)',
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue(),
                $alpha / 100
            ));
        }

        if ($color instanceof Cmyk) {
            return new ImagickPixel(sprintf(
                'cmyka(%d, %d, %d, %d, %F)',
                $color->getCyan(),
                $color->getMagenta(),
                $color->getYellow(),
                $color->getBlack(),
                $alpha / 100
            ));
        }

        if ($color instanceof Gray) {
            return new ImagickPixel(sprintf(
                'graya(%d%%, %F)',
                $color->getGray(),
                $alpha / 100
            ));
        }

        return $this->getColorPixel(new Alpha($alpha, $color->toRgb()));
    }
}
