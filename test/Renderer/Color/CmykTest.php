<?php
declare(strict_types = 1);

namespace BaconQrCodeTest\Renderer\Color;

use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;
use BaconQrCode\Renderer\Color\Rgb;
use PHPUnit\Framework\TestCase;

final class CmykTest extends TestCase
{
    /**
     * Tests CMYK to RGB conversion, focusing on correct application of rounding.
     */
    public function testToRgb() : void
    {
        // Pure Black (C:0, M:0, Y:0, K:100) -> RGB(0, 0, 0)
        $cmykBlack = new Cmyk(0, 0, 0, 100);
        $this->assertEquals(new Rgb(0, 0, 0), $cmykBlack->toRgb(), 'CMYK Black to RGB');

        // Pure White (C:0, M:0, Y:0, K:0) -> RGB(255, 255, 255)
        $cmykWhite = new Cmyk(0, 0, 0, 0);
        $this->assertEquals(new Rgb(255, 255, 255), $cmykWhite->toRgb(), 'CMYK White to RGB');

        // Mid Gray (C:0, M:0, Y:0, K:50) -> RGB(128, 128, 128)
        // Check for rounding: 255 * (1 - 0) * (1 - 0.5) = 127.5 -> round(127.5) = 128
        $cmykGray = new Cmyk(0, 0, 0, 50);
        $this->assertEquals(new Rgb(128, 128, 128), $cmykGray->toRgb(), 'CMYK Gray to RGB (rounding check)');

        // Complex Color (Dark Red): C:10, M:80, Y:70, K:30
        // R: round(255 * 0.9 * 0.7) = round(160.65) = 161
        // G: round(255 * 0.2 * 0.7) = round(35.7) = 36
        // B: round(255 * 0.3 * 0.7) = round(53.55) = 54
        $cmykColor = new Cmyk(10, 80, 70, 30);
        $this->assertEquals(new Rgb(161, 36, 54), $cmykColor->toRgb(), 'CMYK Complex Color to RGB');
    }

    public function testToCmyk() : void
    {
        $cmyk = new Cmyk(10, 20, 30, 40);
        $this->assertSame($cmyk, $cmyk->toCmyk(), 'toCmyk should return $this');
    }

    /**
     * Tests CMYK to Gray conversion via RGB.
     */
    public function testToGray() : void
    {
        // White (K:0) -> Gray(100)
        $cmykWhite = new Cmyk(0, 0, 0, 0);
        $this->assertEquals(new Gray(100), $cmykWhite->toGray(), 'CMYK White to Gray');

        // Black (K:100) -> Gray(0)
        $cmykBlack = new Cmyk(0, 0, 0, 100);
        $this->assertEquals(new Gray(0), $cmykBlack->toGray(), 'CMYK Black to Gray');

        // Pure Gray (K:50) -> Should result in Gray(50)
        $cmykGray = new Cmyk(0, 0, 0, 50);
        $this->assertEquals(new Gray(50), $cmykGray->toGray(), 'CMYK Gray to Gray');
    }
}
