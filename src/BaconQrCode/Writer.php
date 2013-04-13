<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Exception;
use BaconQrCode\Renderer\RendererInterface;

/**
 * QR code writer.
 */
class Writer
{
    /**
     * Renderer instance.
     *
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * Creates a new writer with a specific renderer.
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Sets the renderer used to create a byte stream.
     *
     * @param  RendererInterface $renderer
     * @return Writer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Gets the renderer used to create a byte stream.
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Writes QR code into a string or file.
     *
     * Content is a string which *should* be encoded in UTF-8, in case there are
     * non ASCII-characters present.
     *
     * The return value depends on whether a filename is supplied. If a filename
     * is supplied, nothing will be returned. Else the byte stream of the
     * renderer will be returned
     *
     * @param  string  $content
     * @param  string  $filename
     * @param  array   $hints
     * @return string|null
     * @throws Exception\InvalidArgumentException
     */
    public function write(
        $content,
        $filename = null,
        $encoding = Encoder::DEFAULT_BYTE_MODE_ECODING,
        $ecLevel = ErrorCorrectionLevel::L
    ) {
        if (strlen($content) === 0) {
            throw new Exception\InvalidArgumentException('Found empty contents');
        }

        $qrCode     = Encoder::encode($content, new ErrorCorrectionLevel($ecLevel), $encoding);
        $byteStream = $this->getRenderer()->render($qrCode);

        if ($filename !== null) {
            file_put_contents($filename, $byteStream);
        } else {
            return $byteStream;
        }
    }
}
