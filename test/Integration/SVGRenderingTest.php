<?php

declare(strict_types=1);

namespace BaconQrCodeTest\Integration;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
final class SVGRenderingTest extends TestCase
{
    public function testGenericQrCode(): void
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $svgCode = $writer->writeString('Hello World!');
        $expected = file_get_contents(__DIR__.'/__snapshots__/files/SVGRenderingTest__testGenericQrCode__1.svg');

        $this->assertEquals($expected, $svgCode);
    }

    //    SVGRenderingTest__testQrWithGradientGeneratesDifferentIdsForDifferentGradients_horizontal
    //SVGRenderingTest__testQrWithGradientGeneratesDifferentIdsForDifferentGradients_vertical
    public function testQrWithGradientGeneratesDifferentIdsForDifferentGradients()
    {
        $types = ['HORIZONTAL', 'VERTICAL'];
        foreach ($types as $type) {
            $gradient = new Gradient(
                new Rgb(0, 0, 0),
                new Rgb(255, 0, 0),
                GradientType::$type()
            );
            $renderer = new ImageRenderer(
                new RendererStyle(
                    size: 400,
                    fill: Fill::withForegroundGradient(
                        new Rgb(255, 255, 255),
                        $gradient,
                        EyeFill::inherit(),
                        EyeFill::inherit(),
                        EyeFill::inherit()
                    )
                ),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qr = $writer->writeString('Hello World!');
            $expectedFile = __DIR__ . '/__snapshots__/files/';
            $expectedFile .= 'SVGRenderingTest__testQrWithGradientGeneratesDifferentIdsForDifferentGradients__';
            $expectedFile .= strtolower($type) . '.svg';
            $expected = file_get_contents($expectedFile);

            $this->assertEquals($expected, $qr);
        }
    }
}
