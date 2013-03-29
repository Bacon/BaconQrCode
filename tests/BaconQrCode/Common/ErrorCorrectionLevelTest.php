<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use PHPUnit_Framework_TestCase as TestCase;

class ErrorCorrectionLevelTest extends TestCase
{
    public function testCreation()
    {
        // Should not throw any exceptions here
        new ErrorCorrectionLevel(ErrorCorrectionLevel::H);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::L);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::M);
        new ErrorCorrectionLevel(ErrorCorrectionLevel::Q);

        // But now it should
        $this->setExpectedException(
            'BaconQrCode\Exception\UnexpectedValueException',
            'Value not a const in enum BaconQrCode\Common\ErrorCorrectionLevel'
        );
        new ErrorCorrectionLevel(4);
    }
}