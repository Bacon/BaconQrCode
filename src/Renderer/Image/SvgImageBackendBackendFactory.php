<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\ColorInterface;

final class SvgImageBackendBackendFactory implements ImageBackendFactoryInterface
{
    public function __invoke(int $size, ColorInterface $backgroundColor) : ImageBackendInterface
    {
        return new SvgImageBackendBackend($size, $backgroundColor);
    }
}
