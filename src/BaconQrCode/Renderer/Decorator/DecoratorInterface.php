<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Decorator;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\Backend\BackendInterface;

/**
 * Decorator interface.
 */
interface DecoratorInterface
{
    /**
     * Pre-process a QR code.
     *
     * @param  QrCode $qrCode
     * @return void
     */
    public function preProcess(QrCode $qrCode);

    /**
     * Post-process a QR code.
     *
     * @param  QrCode           $qrCode
     * @param  BackendInterface $backend
     * @param  integer          $outputWidth
     * @param  integer          $outputHeight
     * @param  integer          $leftPadding
     * @param  integer          $topPadding
     * @param  integer          $multiple
     * @return void
     */
    public function postProcess(
        QrCode $qrCode,
        BackendInterface $backend,
        $outputWidth,
        $outputHeight,
        $leftPadding,
        $topPadding,
        $multiple
    );
}