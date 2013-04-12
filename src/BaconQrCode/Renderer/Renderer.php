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
use BaconQrCode\Renderer\Backend;
use BaconQrCode\Renderer\Color;
use BaconQrCode\Renderer\Decorator\DecoratorInterface;

/**
 * Default renderer.
 */
class Renderer implements RendererInterface
{
    /**
     * Backend to use for creating the bytestream.
     *
     * @var Backend\BackendInterface
     */
    protected $backend;

    /**
     * Background color.
     *
     * @var Color\ColorInterface
     */
    protected $backgroundColor;

    /**
     * Foreground color.
     *
     * @var Color\ColorInterface
     */
    protected $foregroundColor;

    /**
     * Decorators used on QR codes.
     *
     * @var array
     */
    protected $decorators = array();

    /**
     * Creates a new renderer with a given backend.
     *
     * @param Backend\BackendInterface $backend
     */
    public function __construct(Backend\BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Sets background color.
     *
     * @param  Color\ColorInterface $color
     * @return void
     */
    public function setBackgroundColor(Color\ColorInterface $color)
    {
        $this->backgroundColor = $color;
    }

    /**
     * Gets background color.
     *
     * @return Color\ColorInterface
     */
    public function getBackgroundColor()
    {
        if ($this->backgroundColor === null) {
            $this->backgroundColor = new Color\Gray(100);
        }

        return $this->backgroundColor;
    }

    /**
     * Sets foreground color.
     *
     * @param  Color\ColorInterface $color
     * @return void
     */
    public function setForegroundColor(Color\ColorInterface $color)
    {
        $this->foregroundColor = $color;
    }

    /**
     * Gets foreground color.
     *
     * @return Color\ColorInterface
     */
    public function getForegroundColor()
    {
        if ($this->foregroundColor === null) {
            $this->foregroundColor = new Color\Gray(0);
        }

        return $this->foregroundColor;
    }

    /**
     * Adds a decorator to the renderer.
     *
     * @param  DecoratorInterface $decorator
     * @return void
     */
    public function addDecorator(DecoratorInterface $decorator)
    {
        $this->decorators[] = $decorator;
    }

    /**
     * render(): defined by RendererInterface.
     *
     * @see    RendererInterface::render()
     * @param  QrCode      $qrCode
     * @param  integer     $width
     * @param  integer     $height
     * @param  integer     $margin
     * @return mixed
     */
    public function render(QrCode $qrCode, $width, $height, $margin)
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

        $this->backend->init($outputWidth, $outputHeight, $multiple);
        $this->backend->addColor('background', $this->getBackgroundColor());
        $this->backend->addColor('foreground', $this->getForegroundColor());
        $this->backend->drawBackground('background');

        foreach ($this->decorators as $decorator) {
            $decorator->preProcess($qrCode);
        }

        for ($inputY = 0, $outputY = $topPadding; $inputY < $inputHeight; $inputY++, $outputY += $multiple) {
            for ($inputX = 0, $outputX = $leftPadding; $inputX < $inputWidth; $inputX++, $outputX += $multiple) {
                if ($input->get($inputX, $inputY) === 1) {
                    $this->backend->drawBlock($outputX, $outputY, 'foreground');
                }
            }
        }

        foreach ($this->decorators as $decorator) {
            $decorator->postProcess(
                $qrCode,
                $this->backend,
                $outputWidth,
                $outputHeight,
                $leftPadding,
                $topPadding,
                $multiple
            );
        }

        return $this->backend->getByteStream();
    }
}