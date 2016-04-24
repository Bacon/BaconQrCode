<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2016 Tim Tegeler
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Exception;
use BaconQrCode\Renderer\Color\ColorInterface;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * JPG backend.
 */
class Jpg extends AbstractRenderer
{

    /**
     * Image resource used when drawing.
     *
     * @var Imagick
     */
    protected $image;

    /**
     * Colors used for drawing.
     *
     * @var ImagickPixel[]
     */
    protected $colors = array();

    /**
     * Draw resource for drawing dots.
     *
     * @var ImagickDraw
     */
    protected $draw;

    /**
     * Flag for determining if cmyk should be used for colorspace.
     *
     * @var bool
     */
    protected $cmyk = false;

    /**
     * init(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::init()
     * @return void
     */
    public function init()
    {
        $this->image = new \Imagick();
        $this->draw = new \ImagickDraw();
    }

    /**
     * addColor(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::addColor()
     * @param  string $id
     * @param  ColorInterface $color
     * @return void
     * @throws Exception\RuntimeException
     */
    public function addColor($id, ColorInterface $color)
    {
        if ($this->image === null) {
            throw new Exception\RuntimeException('Colors can only be added after init');
        }

        $pixel = new \ImagickPixel();

        if ($this->cmyk) {
            $color = $color->toCmyk();
            $pixel->setColorValue(Imagick::COLOR_CYAN, $color->getCyan() / 100);
            $pixel->setColorValue(Imagick::COLOR_MAGENTA, $color->getMagenta() / 100);
            $pixel->setColorValue(Imagick::COLOR_YELLOW, $color->getYellow() / 100);
            $pixel->setColorValue(Imagick::COLOR_BLACK, $color->getBlack() / 100);
        } else {
            $color = $color->toRgb();
            $pixel->setColorValue(Imagick::COLOR_RED, $color->getRed() / 255);
            $pixel->setColorValue(Imagick::COLOR_GREEN, $color->getGreen() / 255);
            $pixel->setColorValue(Imagick::COLOR_BLUE, $color->getBlue() / 255);
        }

        $this->colors[$id] = $pixel;
    }

    /**
     * drawBackground(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::drawBackground()
     * @param  string $colorId
     * @return void
     */
    public function drawBackground($colorId)
    {
        $this->image->newImage($this->finalWidth, $this->finalHeight, $this->colors['background']);
        $this->image->setImageFormat("jpg");
        $this->image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $this->image->setImageCompressionQuality(100);
        if ($this->cmyk) {
            $this->image->setImageColorspace(Imagick::COLORSPACE_CMYK);
        } else {
            $this->image->setImageColorspace(Imagick::COLORSPACE_RGB);
        }
    }

    /**
     * drawBlock(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::drawBlock()
     * @param  integer $x
     * @param  integer $y
     * @param  string $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId)
    {
        $this->draw->setFillColor($this->colors[$colorId]);
        if ($this->blockSize > 1) {
            $this->draw->rectangle($x, $y, $x + $this->blockSize - 1, $y + $this->blockSize - 1);
        } else {
            $this->draw->point($x, $y);
        }
    }

    /**
     * getByteStream(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
        $this->image->drawImage($this->draw);
        return $this->image->getImageBlob();
    }

    /**
     * Sets whether cmyk should be used as color space.
     *
     * @param  boolean $flag
     * @return AbstractRenderer
     */
    public function setCmyk($flag)
    {
        $this->cmyk = $flag;
        return $this;
    }
}