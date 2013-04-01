<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\QrCode;

/**
 * Bitmap renderer.
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * Width of the resulting drawing.
     *
     * @var integer
     */
    protected $width;

    /**
     * Heigth of the resulting drawing.
     *
     * @var integer
     */
    protected $height;

    /**
     * Size of each individual block.
     *
     * @var integer
     */
    protected $blockSize;

    /**
     * render(): defined by RendererInterface.
     *
     * @see    RendererInterface::render()
     * @param  QrCode      $qrCode
     * @param  integer     $width
     * @param  integer     $height
     * @param  integer     $margin
     * @param  string|null $filename
     * @return mixed
     */
    public function render(QrCode $qrCode, $width, $height, $margin, $filename = null)
    {
        $input        = $qrCode->getMatrix();
        $inputWidth   = $input->getWidth();
        $inputHeight  = $input->getHeight();
        $qrWidth      = $inputWidth + ($margin << 1);
        $qrHeight     = $inputHeight + ($margin << 1);
        $outputWidth  = max($width, $qrWidth);
        $outputHeight = max($height, $qrHeight);
        $multiple     = (int) min($outputWidth / $qrWidth, $outputHeight / $qrHeight);

        // Padding includes both the quiet zone and the extra white pixels to
        // accommodate the requested dimensions. For example, if input is 25x25
        // the QR will be 33x33 including the quiet zone. If the requested size
        // is 200x160, the multiple will be 4, for a QR of 132x132. These will
        // handle all the padding from 100x100 (the actual QR) up to 200x160.
        $leftPadding = (int) (($outputWidth - ($inputWidth * $multiple)) / 2);
        $topPadding  = (int) (($outputHeight - ($inputHeight * $multiple)) / 2);

        $this->width     = $outputWidth;
        $this->height    = $outputHeight;
        $this->blockSize = $multiple;

        $this->initDrawing();

        for ($inputY = 0, $outputY = $topPadding; $inputY < $inputHeight; $inputY++, $outputY += $multiple) {
            for ($inputX = 0, $outputX = $leftPadding; $inputX < $inputWidth; $inputX++, $outputX += $multiple) {
                if ($input->get($inputX, $inputY) === 1) {
                    $this->drawSquare($outputX, $outputY, $multiple);
                }
            }
        }

        return $this->finishDrawing($filename);
    }

    /**
     * Initiate drawing.
     *
     * @return void
     */
    abstract protected function initDrawing();

    /**
     * Draw a square at given position.
     *
     * @param  integer $x
     * @param  integer $y
     * @return void
     */
    abstract protected function drawSquare($x, $y);

    /**
     * Finish drawing.
     *
     * When filename is given, write result to the file, else return it.
     *
     * @param  string|null $filename
     * @return mixed
     */
    abstract protected function finishDrawing($filename = null);
}