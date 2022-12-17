<?php

declare(strict_types=1);

namespace BaconQrCodeTest\Integration;

use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\SimpleCircleEye;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\GDImageBackEnd;
use BaconQrCode\Renderer\Module\DotsModule;
use BaconQrCode\Renderer\Module\RoundnessModule;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

#[Group('integration')]
final class GDLibRenderingTest extends TestCase
{
    use MatchesSnapshots;

    #[RequiresPhpExtension('gd')]
    public function testGenericQrCode(): void
    {
        $renderer = new GDLibRenderer(400);
        $writer = new Writer($renderer);
        $tempName = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer->writeFile('Hello World!', $tempName);

        $this->assertMatchesFileSnapshot($tempName);
        unlink($tempName);
    }

    #[RequiresPhpExtension('gd')]
    public function testDifferentColorsQrCode(): void
    {
        $renderer = new GDLibRenderer(
            400,
            10,
            'png',
            9,
            Fill::withForegroundColor(
                new Alpha(25, new Rgb(0, 0, 0)),
                new Rgb(0, 0, 0),
                new EyeFill(new Rgb(220, 50, 50), new Alpha(50, new Rgb(220, 50, 50))),
                new EyeFill(new Rgb(50, 220, 50), new Alpha(50, new Rgb(50, 220, 50))),
                new EyeFill(new Rgb(50, 50, 220), new Alpha(50, new Rgb(50, 50, 220))),
            )
        );
        $writer = new Writer($renderer);
        $tempName = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer->writeFile('Hello World!', $tempName);

        $this->assertMatchesFileSnapshot($tempName);
        unlink($tempName);
    }


    #[RequiresPhpExtension('gd')]
    public function testFailsOnGradient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('GDLibRenderer does not support gradients');

        new GDLibRenderer(
            400,
            10,
            'png',
            9,
            Fill::withForegroundGradient(
                new Alpha(25, new Rgb(0, 0, 0)),
                new Gradient(new Rgb(255, 255, 0), new Rgb(255, 0, 255), GradientType::DIAGONAL()),
                new EyeFill(new Rgb(220, 50, 50), new Alpha(50, new Rgb(220, 50, 50))),
                new EyeFill(new Rgb(50, 220, 50), new Alpha(50, new Rgb(50, 220, 50))),
                new EyeFill(new Rgb(50, 50, 220), new Alpha(50, new Rgb(50, 50, 220))),
            )
        );
    }

    #[RequiresPhpExtension('gd')]
    public function testFailsOnInvalidFormat(): void
    {
        $renderer = new GDLibRenderer(400, 4, 'tiff');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Supported image formats are jpeg, png and gif, got: tiff');

        $writer = new Writer($renderer);
        $tempName = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer->writeFile('Hello World!', $tempName);
    }
}
