<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Module;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Path\Path;

interface ModuleInterface
{
    public function createPath(ByteMatrix $matrix) : Path;
}
