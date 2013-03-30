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
 * Renderer interface.
 */
interface RendererInterface
{
    /**
     * Render a QR code.
     *
     * If filename is set to null, return the result, else write to the
     * specified file.
     *
     * @param  QrCode      $qrCode
     * @param  integer     $width
     * @param  integer     $height
     * @param  integer     $margin
     * @param  string|null $filename
     * @return mixed
     */
    public function render(QrCode $qrCode, $width, $height, $margin, $filename = null);
}
