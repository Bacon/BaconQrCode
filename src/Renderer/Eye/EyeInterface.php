<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Eye;

use BaconQrCode\Renderer\Path\Path;

interface EyeInterface
{
    public function getExternalPath() : Path;

    public function getInternalPath() : Path;
}
