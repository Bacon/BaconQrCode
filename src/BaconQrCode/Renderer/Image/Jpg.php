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
     * @var ColorInterface[]
     */
    protected $colors = array();

    /**
     * Flag for determining if cmyk should be used for colorSpace.
     *
     * @var bool
     */
    protected $cmyk = false;

    /**
     * ColorArray used for insert.
     *
     * @var array
     */
    private $colorArray = null;

    /**
     * init(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::init()
     * @return void
     */
    public function init()
    {
        $this->image = new \Imagick();
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

        $this->colors[$id] = $color;
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
        $color = $this->colors['background'];

        if ($this->cmyk) {

            $color = $color->toCmyk();

            $pixel = new ImagickPixel(sprintf('cmyk(%s,%s,%s,%s)',
                $color->getCyan(),
                $color->getMagenta(),
                $color->getYellow(),
                $color->getBlack()));

            $colorSpace = Imagick::COLORSPACE_CMYK;
        } else {

            $color = $color->toRgb();

            $pixel = new ImagickPixel(sprintf('rgb(%s,%s,%s)',
                $color->getRed(),
                $color->getGreen(),
                $color->getBlue()));

            $colorSpace = Imagick::COLORSPACE_RGB;
        }
        $this->image->newImage($this->finalWidth, $this->finalHeight, $pixel);
        $this->image->setImageFormat('jpeg');
        $this->image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $this->image->setImageCompressionQuality(100);
        $this->image->setImageColorspace($colorSpace);
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
        $color = $this->colors[$colorId];

        if ($this->colorArray == null) {
            $this->buildColorArray($color);
        }

        $this->image->importImagePixels($x,
            $y,
            $this->blockSize,
            $this->blockSize,
            $this->cmyk ? 'CMYK' : 'RGB',
            Imagick::PIXEL_CHAR,
            $this->colorArray);
    }

    /**
     * getByteStream(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
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

    /**
     * Builds colorArray for importImagePixels.
     *
     * @param ColorInterface $color
     */
    private function buildColorArray(ColorInterface $color)
    {
        if ($this->cmyk) {
            $color = $color->toCmyk();
            $colorArray = array();
            for ($i = 0; $i < $this->blockSize * $this->blockSize; $i++) {
                $colorArray[] = (int)round($color->getCyan() * 2.55);
                $colorArray[] = (int)round($color->getMagenta() * 2.55);
                $colorArray[] = (int)round($color->getYellow() * 2.55);
                $colorArray[] = (int)round($color->getBlack() * 2.55);
            }
        } else {
            $color = $color->toRgb();
            $colorArray = array();
            for ($i = 0; $i < $this->blockSize * $this->blockSize; $i++) {
                $colorArray[] = $color->getRed();
                $colorArray[] = $color->getGreen();
                $colorArray[] = $color->getBlue();
            }
        }
        $this->colorArray = $colorArray;
    }

}