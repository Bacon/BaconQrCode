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
 * Bitmap renderer.
 */
class Bitmap extends AbstractRenderer
{
    /**
     * Image resource used when drawing.
     *
     * @var resource
     */
    protected $image;

    /**
     * Background color index for the QR code.
     *
     * @var integer
     */
    protected $backgroundColor;

    /**
     * Foreground color index for the QR code.
     *
     * @var integer
     */
    protected $foregroundColor;

    /**
     * initDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::initDrawing()
     * @return void
     */
    protected function initDrawing()
    {
        $this->image           = imagecreatetruecolor($this->width, $this->height);
        $this->backgroundColor = imagecolorallocate($this->image, 255, 255, 255);
        $this->foregroundColor  = imagecolorallocate($this->image, 0, 0, 0);

        imagefill($this->image, 0, 0, $this->backgroundColor);
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
        imagefilledrectangle(
            $this->image,
            $x,
            $y,
            $x + $this->blockSize - 1,
            $y + $this->blockSize - 1,
            $this->foregroundColor
        );
    }

    /**
     * finishDrawing(): defined by AbstractRenderer.
     *
     * @see    AbstractRenderer::finishDrawing()
     * @param  string|null $filename
     * @return resource|null
     */
    protected function finishDrawing($filename = null)
    {
        if ($filename !== null) {
            imagepng($this->image, $filename);
            imagedestroy($this->image);
        } else {
            return $this->image;
        }
    }
}