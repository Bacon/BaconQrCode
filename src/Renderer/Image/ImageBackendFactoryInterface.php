<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\ColorInterface;

interface ImageBackendFactoryInterface
{
    public function __invoke(int $size, ColorInterface $backgroundColor) : ImageBackendInterface;
}
