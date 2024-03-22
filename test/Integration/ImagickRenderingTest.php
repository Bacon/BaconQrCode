<?php
declare(strict_types = 1);

namespace BaconQrCodeTest\Integration;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Eye\PointyEye;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\SquareModule;
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
final class ImagickRenderingTest extends TestCase
{
    use MatchesSnapshots;

    #[RequiresPhpExtension('imagick')]
    public function testGenericQrCode() : void
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $tempName = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer->writeFile('Hello World!', $tempName);

        $this->assertMatchesFileSnapshot($tempName);
        unlink($tempName);
    }

    #[RequiresPhpExtension('imagick')]
    public function testIssue79() : void
    {
        $eye = SquareEye::instance();
        $squareModule = SquareModule::instance();

        $eyeFill = new EyeFill(new Rgb(100, 100, 55), new Rgb(100, 100, 255));
        $gradient = new Gradient(new Rgb(100, 100, 55), new Rgb(100, 100, 255), GradientType::HORIZONTAL());

        $renderer = new ImageRenderer(
            new RendererStyle(
                400,
                2,
                $squareModule,
                $eye,
                Fill::withForegroundGradient(new Rgb(255, 255, 255), $gradient, $eyeFill, $eyeFill, $eyeFill)
            ),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $tempName = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer->writeFile('https://apiroad.net/very-long-url', $tempName);

        $this->assertMatchesFileSnapshot($tempName);
        unlink($tempName);
    }

    public function testIssue105() : void
    {
        $squareModule = SquareModule::instance();
        $pointyEye = PointyEye::instance();

        $renderer1 = new ImageRenderer(
            new RendererStyle(
                400,
                2,
                $squareModule,
                $pointyEye,
                Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 255))
            ),
            new ImagickImageBackEnd()
        );
        $writer1 = new Writer($renderer1);
        $tempName1 = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer1->writeFile('rotation without eye color', $tempName1);

        $this->assertMatchesFileSnapshot($tempName1);
        unlink($tempName1);

        $eyeFill = new EyeFill(new Rgb(255, 0, 0), new Rgb(0, 255, 0));

        $renderer2 = new ImageRenderer(
            new RendererStyle(
                400,
                2,
                $squareModule,
                $pointyEye,
                Fill::withForegroundColor(new Rgb(255, 255, 255), new Rgb(0, 0, 255), $eyeFill, $eyeFill, $eyeFill)
            ),
            new ImagickImageBackEnd()
        );
        $writer2 = new Writer($renderer2);
        $tempName2 = tempnam(sys_get_temp_dir(), 'test') . '.png';
        $writer2->writeFile('rotation with eye color', $tempName2);

        $this->assertMatchesFileSnapshot($tempName2);
        unlink($tempName2);
    }
}
