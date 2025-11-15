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
use Spatie\Snapshots\MatchesSnapshots;

#[Group('integration')]
final class SVGRenderingTest extends TestCase
{
    use MatchesSnapshots;

    public function testGenericQrCode(): void
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString('Hello World!');

        $this->assertMatchesXmlSnapshot($svg);
    }

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
            $svg = $writer->writeString('Hello World!');

            $this->assertMatchesXmlSnapshot($svg);
        }
    }
}
