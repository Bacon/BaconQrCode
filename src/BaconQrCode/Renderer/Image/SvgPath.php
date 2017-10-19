<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Exception;
use BaconQrCode\Renderer\Color\ColorInterface;
use SimpleXMLElement;

/**
 * SVG "Path" backend.
 */
class SvgPath extends AbstractRenderer
{
    /**
     * SVG resource.
     *
     * @var SimpleXMLElement
     */
    protected $svg;

    /**
     * Colors used for drawing.
     *
     * @var array
     */
    protected $colors = array();

    /**
     * SVG path commands used for drawing.
     *
     * Two-dimensional array where first dimension is color
     * and second dimension contains ordered list of SVG path commands.
     *
     * @var array
     */
    protected $pathCommands = array();

    /**
     * init(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::init()
     * @return void
     */
    public function init()
    {
        $this->svg = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg"/>'
        );
        $this->svg->addAttribute('version', '1.1');
        $this->svg->addAttribute('width', $this->finalWidth . 'px');
        $this->svg->addAttribute('height', $this->finalHeight . 'px');
        $this->svg->addAttribute('viewBox', '0 0 ' . $this->finalWidth . ' ' . $this->finalHeight);
    }

    /**
     * addColor(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::addColor()
     * @param  string         $id
     * @param  ColorInterface $color
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function addColor($id, ColorInterface $color)
    {
        $this->colors[$id] = (string) $color->toRgb();
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
        $background = $this->drawPath(
            array(
                'M 0 0',
                sprintf('H %s', $this->finalWidth),
                sprintf('V %s', $this->finalHeight),
                'H 0',
                'V 0',
            ),
            $this->colors[$colorId]
        );

        $background->addAttribute('id', 'bg');
    }

    /**
     * drawBlock(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::drawBlock()
     * @param  integer $x
     * @param  integer $y
     * @param  string  $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId)
    {
        if (!isset($this->pathCommands[$colorId])) {
            $this->pathCommands[$colorId] = array();
        }

        $this->pathCommands[$colorId][] = sprintf('M %s %s', $x, $y);
        $this->pathCommands[$colorId][] = sprintf('h%s', $this->blockSize);
        $this->pathCommands[$colorId][] = sprintf('v%s', $this->blockSize);
        $this->pathCommands[$colorId][] = sprintf('h-%s', $this->blockSize);
        $this->pathCommands[$colorId][] = sprintf('v-%s', $this->blockSize);
    }

    /**
     * getByteStream(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
        $this->renderPaths();

        return $this->svg->asXML();
    }

    /**
     * Renders all "prepared" paths into the SVG.
     */
    protected function renderPaths()
    {
        foreach ($this->pathCommands as $colorId => $commands) {
            $this->drawPath($commands, $this->colors[$colorId]);
        }
    }

    /**
     * Draws a path into the SVG.
     *
     * @param array $commands
     * @param string $fillColor
     *
     * @return SimpleXMLElement
     */
    protected function drawPath(array $commands, $fillColor)
    {
        $path = $this->svg->addChild('path');
        $path->addAttribute('fill', '#' . $fillColor);
        $path->addAttribute('d', implode(' ', $commands));

        return $path;
    }
}
