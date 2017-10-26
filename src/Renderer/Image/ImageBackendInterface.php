<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Path\Path;

interface ImageBackendInterface
{
    public function scale(float $size) : void;

    public function translate(float $x, float $y) : void;

    public function rotate(int $degrees) : void;

    public function push() : void;

    public function pop() : void;

    public function drawPath(Path $path, ColorInterface $color) : void;

    public function getBlob() : string;
}
