<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Module;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Module\EdgeIterator\EdgeIterator;
use BaconQrCode\Renderer\Path\Path;

final class StrongRoundnessModule implements ModuleInterface
{
    public function createPath(ByteMatrix $matrix) : Path
    {
        $path = new Path();

        foreach (new EdgeIterator($matrix) as $edge) {
            $edge->simplify();
            $points = $edge->getPoints();
            $length = count($points);

            $currentPoint = $points[0];
            $nextPoint = $points[1];
            $horizontal = ($currentPoint[1] === $nextPoint[1]);

            if ($horizontal) {
                $right = $nextPoint[0] > $currentPoint[0];
                $path = $path->moveTo($currentPoint[0] + ($right ? .5 : -.5), $currentPoint[1]);
            } else {
                $up = $nextPoint[0] < $currentPoint[0];
                $path = $path->moveTo($currentPoint[0], $currentPoint[1] + ($up ? -.5 : .5));
            }

            for ($i = 1; $i <= $length; ++$i) {
                if ($i === $length) {
                    $previousPoint = $points[$length - 1];
                    $currentPoint = $points[0];
                    $nextPoint = $points[1];
                } else {
                    $previousPoint = $points[(0 === $i ? $length : $i) - 1];
                    $currentPoint = $points[$i];
                    $nextPoint = $points[($length - 1 === $i ? -1 : $i) + 1];
                }

                $horizontal = ($previousPoint[1] === $currentPoint[1]);

                if ($horizontal) {
                    $right = $previousPoint[0] < $currentPoint[0];
                    $up = $nextPoint[1] < $currentPoint[1];
                    $sweep = ($up xor $right);

                    if (($right && $previousPoint[0] !== $currentPoint[0] - 1)
                        || (! $right && $previousPoint[0] - 1 !== $currentPoint[0])
                    ) {
                        $path = $path->lineTo($currentPoint[0] + ($right ? -.5 : .5), $currentPoint[1]);
                    }

                    $path = $path->ellipticArc(
                        .5,
                        .5,
                        0,
                        false,
                        $sweep,
                        $currentPoint[0],
                        $currentPoint[1] + ($up ? -.5 : .5)
                    );
                } else {
                    $up = $previousPoint[1] > $currentPoint[1];
                    $right = $nextPoint[0] > $currentPoint[0];
                    $sweep = ! ($up xor $right);

                    if (($up && $previousPoint[1] !== $currentPoint[1] + 1)
                        || (! $up && $previousPoint[0] + 1 !== $currentPoint[0])
                    ) {
                        $path = $path->lineTo($currentPoint[0], $currentPoint[1] + ($up ? .5 : -.5));
                    }

                    $path = $path->ellipticArc(
                        .5,
                        .5,
                        0,
                        false,
                        $sweep,
                        $currentPoint[0] + ($right ? .5 : -.5),
                        $currentPoint[1]
                    );
                }
            }

            $path = $path->close();
        }

        return $path;
    }
}
