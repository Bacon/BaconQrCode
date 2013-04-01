<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer;

use SimpleXMLElement;

/**
 * SVG renderer.
 */
class Svg extends AbstractRenderer
{
    /**
     * SVG resource.
     *
     * @var SimpleXMLElement
     */
    protected $svg;

    /**
     * initDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::initDrawing()
     * @return void
     */
    protected function initDrawing()
    {
        $this->svg = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"/>'
        );
        $this->svg->addAttribute('version', '1.1');
        $this->svg->addAttribute('width', $this->width . 'px');
        $this->svg->addAttribute('height', $this->height . 'px');

        $defs = $this->svg->addChild('defs');
        $rect = $defs->addChild('rect');
        $rect->addAttribute('id', 'b');
        $rect->addAttribute('width', $this->blockSize);
        $rect->addAttribute('height', $this->blockSize);
        $rect->addAttribute('fill', '#000000');

        $rect = $this->svg->addChild('rect');
        $rect->addAttribute('x', 0);
        $rect->addAttribute('y', 0);
        $rect->addAttribute('width', $this->width);
        $rect->addAttribute('height', $this->height);
        $rect->addAttribute('fill', '#ffffff');
    }

    /**
     * drawSquare(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::drawSquare()
     * @param  integer $x
     * @param  integer $y
     * @return void
     */
    protected function drawSquare($x, $y)
    {
        $use = $this->svg->addChild('use');
        $use->addAttribute('x', $x);
        $use->addAttribute('y', $y);
        $use->addAttribute('xlink:href', '#b', 'http://www.w3.org/1999/xlink');
    }

    /**
     * finishDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::finishDrawing()
     * @param  string|null $filename
     * @return SimpleXMLElement|null
     */
    protected function finishDrawing($filename = null)
    {
        if ($filename !== null) {
            $this->svg->asXML($filename);
        } else {
            return $this->svg;
        }
    }
}