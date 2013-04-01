<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer;

/**
 * EPS renderer.
 */
class Eps extends AbstractRenderer
{
    /**
     * EPS string.
     *
     * @var string
     */
    protected $eps;

    /**
     * initDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::initDrawing()
     * @return void
     */
    protected function initDrawing()
    {
        $this->eps = "%!PS-Adobe-3.0 EPSF-3.0\n";
        $this->eps .= "%%BoundingBox: 0 0 " . $this->width . " " . $this->height . "\n";

        $this->eps .= "/F { rectfill } def\n";
        $this->eps .= "1 setgray\n";
        $this->eps .= "0 0 " . $this->width . " " . $this->height . " F\n";
        $this->eps .= "0 setgray\n";
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
        $this->eps .= $x . " " . ($this->height - $y) . " " . $this->blockSize . " " . $this->blockSize . " F\n";
    }

    /**
     * finishDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::finishDrawing()
     * @param  string|null $filename
     * @return string|null
     */
    protected function finishDrawing($filename = null)
    {
        if ($filename !== null) {
            file_put_contents($filename, $this->eps);
        } else {
            return $this->eps;
        }
    }
}