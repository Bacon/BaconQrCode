<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Eye;

use BaconQrCode\Renderer\Path\Path;

final class SimpleCircleEye implements EyeInterface
{
    public function getExternalPath() : Path
    {
        return (new Path())
            ->moveTo(-3.5, -3.5)
            ->lineTo(3.5, -3.5)
            ->lineTo(3.5, 3.5)
            ->lineTo(-3.5, 3.5)
            ->close()
            ->moveTo(-2.5, -2.5)
            ->lineTo(-2.5, 2.5)
            ->lineTo(2.5, 2.5)
            ->lineTo(2.5, -2.5)
            ->close()
        ;
    }

    public function getInternalPath() : Path
    {
        return (new Path())
            ->moveTo(1.5, 0)
            ->ellipticArc(1.5, 1.5, 0., false, true, 0., 1.5)
            ->ellipticArc(1.5, 1.5, 0., false, true, -1.5, 0.)
            ->ellipticArc(1.5, 1.5, 0., false, true, 0., -1.5)
            ->ellipticArc(1.5, 1.5, 0., false, true, 1.5, 0.)
            ->close()
        ;
    }
}
