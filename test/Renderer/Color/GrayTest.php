<?php
declare(strict_types = 1);

namespace BaconQrCodeTest\Renderer\Color;

use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use PHPUnit\Framework\TestCase;

final class GrayTest extends TestCase
{
    /**
     * Tests Gray to RGB conversion, focusing on:
     * 1. Using 255/100 instead of 2.55 to avoid floating-point precision loss.
     * 2. Correct application of rounding.
     */
    public function testToRgb() : void
    {
        // Black (0) -> RGB(0, 0, 0)
        $grayBlack = new Gray(0);
        $this->assertEquals(new Rgb(0, 0, 0), $grayBlack->toRgb(), 'Gray Black to RGB');

        // White (100) -> RGB(255, 255, 255)
        // 100 * 255 / 100 = 255
        $grayWhite = new Gray(100);
        $this->assertEquals(new Rgb(255, 255, 255), $grayWhite->toRgb(), 'Gray White to RGB');

        // Midpoint (50) -> RGB(128, 128, 128)
        // Check for rounding: 50 * 255 / 100 = 127.5 -> round(127.5) = 128
        $grayMiddle = new Gray(50);
        $this->assertEquals(new Rgb(128, 128, 128), $grayMiddle->toRgb(), 'Gray 50 to RGB (rounding check)');

        // Custom value checking rounding
        // 33 * 255 / 100 = 84.15 -> round(84.15) = 84
        $grayCustom = new Gray(33);
        $this->assertEquals(new Rgb(84, 84, 84), $grayCustom->toRgb(), 'Gray Custom to RGB');
    }

    /**
     * Tests Gray to CMYK conversion (K=100-Gray).
     */
    public function testToCmyk() : void
    {
        // Black (0) -> K:100
        $grayBlack = new Gray(0);
        $this->assertEquals(new Cmyk(0, 0, 0, 100), $grayBlack->toCmyk(), 'Gray Black to CMYK');

        // White (100) -> K:0
        $grayWhite = new Gray(100);
        $this->assertEquals(new Cmyk(0, 0, 0, 0), $grayWhite->toCmyk(), 'Gray White to CMYK');

        // Middle (50) -> K:50
        $grayMiddle = new Gray(50);
        $this->assertEquals(new Cmyk(0, 0, 0, 50), $grayMiddle->toCmyk(), 'Gray Middle to CMYK');
    }

    public function testToGray() : void
    {
        $gray = new Gray(75);
        $this->assertSame($gray, $gray->toGray(), 'toGray should return $this');
    }
}
