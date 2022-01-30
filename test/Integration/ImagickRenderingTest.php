<?php
declare(strict_types = 1);

namespace BaconQrCodeTest\Integration;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class ImagickRenderingTest extends TestCase
{
    use MatchesSnapshots;

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
}
