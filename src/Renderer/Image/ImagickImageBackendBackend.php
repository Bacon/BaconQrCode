<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Path\Path;
use Imagick;
use ImagickDraw;
use ImagickPixel;

final class ImagickImageBackendBackend implements ImageBackendInterface
{
    /**
     * @var Imagick
     */
    private $image;

    /**
     * @var ImagickDraw
     */
    private $draw;

    public function __construct(
        int $size,
        ColorInterface $backgroundColor,
        string $imageFormat = 'png',
        int $compressionQuality = 100
    ) {
        $this->image = new Imagick();
        $this->image->newImage($size, $size, $this->getColorPixel($backgroundColor));
        $this->image->setImageFormat($imageFormat);
        $this->image->setCompressionQuality($compressionQuality);
        $this->draw = new ImagickDraw();
    }

    public function scale(float $size) : void
    {
        $this->draw->scale($size, $size);
    }

    public function translate(float $x, float $y) : void
    {
        $this->draw->translate($x, $y);
    }

    public function rotate(int $degrees) : void
    {
        $this->draw->rotate($degrees);
    }

    public function push() : void
    {
        $this->draw->push();
    }

    public function pop() : void
    {
        $this->draw->pop();
    }

    public function drawPath(Path $path, ColorInterface $color) : void
    {
        $this->draw->setFillColor($this->getColorPixel($color));
        $this->draw->pathStart();

        foreach ($path as $operation) {
            switch ($operation[0]) {
                case 'move-to':
                    $this->draw->pathMoveToAbsolute($operation[1], $operation[2]);
                    break;

                case 'line-to':
                    $this->draw->pathLineToAbsolute($operation[1], $operation[2]);
                    break;

                case 'elliptic-arc':
                    $this->draw->pathEllipticArcAbsolute(
                        $operation[1],
                        $operation[2],
                        $operation[3],
                        $operation[4],
                        $operation[5],
                        $operation[6],
                        $operation[7]
                    );
                    break;

                case 'close':
                    $this->draw->pathClose();
                    break;
            }
        }

        $this->draw->pathFinish();
    }

    public function getBlob() : string
    {
        $this->image->drawImage($this->draw);
        return $this->image->getImageBlob();
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
