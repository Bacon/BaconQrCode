<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Backend;

use BaconQrCode\Exception;
use BaconQrCode\Renderer\Color\Rgb;
use SimpleXMLElement;

/**
 * SVG backend.
 */
class Svg implements BackendInterface
{
    /**
     * SVG resource.
     *
     * @var SimpleXMLElement
     */
    protected $svg;

    /**
     * Width of the SVG.
     *
     * @var integer
     */
    protected $width;

    /**
     * Height of the SVG.
     *
     * @var integer
     */
    protected $height;

    /**
     * Block size.
     *
     * @var integer
     */
    protected $blockSize;

    /**
     * Colors used for drawing.
     *
     * @var array
     */
    protected $colors = array();

    /**
     * Prototype IDs.
     *
     * @var array
     */
    protected $prototypeIds = array();

    /**
     * init(): defined by BackendInterface.
     *
     * @see    BackendInterface::init()
     * @param  integer $width
     * @param  integer $height
     * @param  integer $blockSize
     * @return void
     */
    public function init($width, $height, $blockSize)
    {
        $this->svg = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"/>'
        );
        $this->svg->addAttribute('version', '1.1');
        $this->svg->addAttribute('width', $width . 'px');
        $this->svg->addAttribute('height', $height . 'px');
        $this->svg->addChild('defs');

        $this->width     = $width;
        $this->height    = $height;
        $this->blockSize = $blockSize;
    }

    /**
     * addColor(): defined by BackendInterface.
     *
     * @see    BackendInterface::addColor()
     * @param  string $id
     * @param  Rgb    $color
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function addColor($id, $color)
    {
        if (!$color instanceof Rgb) {
            throw new Exception\InvalidArgumentException('Only RGB color allowed in bitmap renderer');
        }

        $this->colors[$id] = (string) $color;
    }

    /**
     * drawBackground(): defined by BackendInterface.
     *
     * @see    BackendInterface::drawBackground()
     * @param  string $colorId
     * @return void
     */
    public function drawBackground($colorId)
    {
        $rect = $this->svg->addChild('rect');
        $rect->addAttribute('x', 0);
        $rect->addAttribute('y', 0);
        $rect->addAttribute('width', $this->width);
        $rect->addAttribute('height', $this->height);
        $rect->addAttribute('fill', '#' . $this->colors[$colorId]);
    }

    /**
     * drawBlock(): defined by BackendInterface.
     *
     * @see    BackendInterface::drawBlock()
     * @param  integer $x
     * @param  integer $y
     * @param  string  $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId)
    {
        $use = $this->svg->addChild('use');
        $use->addAttribute('x', $x);
        $use->addAttribute('y', $y);
        $use->addAttribute(
            'xlink:href',
            $this->getRectPrototypeId($colorId),
            'http://www.w3.org/1999/xlink'
        );
    }

    /**
     * getByteStream(): defined by BackendInterface.
     *
     * @see    BackendInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
        return $this->svg->asXML();
    }

    /**
     * Get the prototype ID for a color.
     *
     * @param  integer $colorId
     * @return string
     */
    protected function getRectPrototypeId($colorId)
    {
        if (!isset($this->prototypeIds[$colorId])) {
            $id = 'r' . dechex(count($this->prototypeIds));

            $rect = $this->svg->defs->addChild('rect');
            $rect->addAttribute('id', $id);
            $rect->addAttribute('width', $this->blockSize);
            $rect->addAttribute('height', $this->blockSize);
            $rect->addAttribute('fill', '#' . $this->colors[$colorId]);

            $this->prototypeIds[$colorId] = '#' . $id;
        }

        return $this->prototypeIds[$colorId];
    }
}
