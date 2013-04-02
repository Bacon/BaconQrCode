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
     * Default quiet zone around QR codes.
     */
    const QUIET_ZONE_SIZE = 4;

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
     * Writes QR code into a string or file.
     *
     * Content is a string which *should* be encoded in UTF-8, in case there are
     * non ASCII-characters present.
     *
     * Hints is an array containing additional options for the writer. These can
     * be:
     *
     * "encoding": the encoding used in byte mode. Default: ISO-8859-1
     * "ecLevel":  the error correction level. Default: ErrorCorrectionLevel::L
     * "margin":   the quiet zone around the QR code. Default: 4
     *
     * The return value depends on whether a filename is supplied. If a filename
     * is supplied, nothing will be returned. Else the return value depends
     * on the chosen renderer.
     *
     * @param  string  $content
     * @param  integer $width
     * @param  integer $height
     * @param  string  $filename
     * @param  array   $hints
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function write(
        $content,
        $width,
        $height,
        $filename = null,
        array $hints = array()
    ) {
        if (strlen($content) === 0) {
            throw new Exception\InvalidArgumentException('Found empty contents');
        }

        if ($width < 0 || $height < 0) {
            throw new Exception\InvalidArgumentException('Requested dimensions are too small');
        }

        $encoding = (isset($hints['encoding']) ? $hints['encoding'] : Encoder::DEFAULT_BYTE_MODE_ECODING);
        $ecLevel  = new ErrorCorrectionLevel(isset($hints['ecLevel']) ? $hints['ecLevel'] : ErrorCorrectionLevel::L);
        $margin   = (isset($hints['margin']) ? $hints['margin'] : self::QUIET_ZONE_SIZE);
        $qrCode   = Encoder::encode($content, $ecLevel, $encoding);

        return $this->renderer->render($qrCode, $width, $height, $margin, $filename);
    }
}
