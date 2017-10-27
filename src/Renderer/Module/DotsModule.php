<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Module;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Path\Path;

final class DotsModule implements ModuleInterface
{
    public function createPath(ByteMatrix $matrix) : Path
    {
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();
        $path = new Path();

        for ($y = 0; $y < $height; ++$y) {
            for ($x = 0; $x < $width; ++$x) {
                if (! $matrix->get($x, $y)) {
                    continue;
                }

                $path = $path
                    ->moveTo($x + 1, $y + .5)
                    ->ellipticArc(.5, .5, 0, false, true, $x + .5, $y + 1)
                    ->ellipticArc(.5, .5, 0, false, true, $x, $y + .5)
                    ->ellipticArc(.5, .5, 0, false, true, $x + .5, $y)
                    ->ellipticArc(.5, .5, 0, false, true, $x + 1, $y + .5)
                    ->close()
                ;
            }
        }

        return $path;
    }
}
