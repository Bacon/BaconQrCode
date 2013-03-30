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
 * Image renderer.
 */
class Image implements RendererInterface
{
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

        $image = imagecreatetruecolor($outputWidth, $outputHeight);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        for ($inputY = 0, $outputY = $topPadding; $inputY < $inputHeight; $inputY++, $outputY += $multiple) {
            for ($inputX = 0, $outputX = $leftPadding; $inputX < $inputWidth; $inputX++, $outputX += $multiple) {
                if ($input->get($inputX, $inputY) === 1) {
                    imagefilledrectangle($image, $outputX, $outputY, $outputX + $multiple, $outputY + $multiple, $black);
                }
            }
        }

        if ($filename !== null) {
            imagepng($image, $filename);
            imagedestroy($image);
        } else {
            return $image;
        }
    }
}