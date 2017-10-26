<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Path;

use IteratorAggregate;
use Traversable;

final class Path implements IteratorAggregate
{
    /**
     * @var array
     */
    private $operations = [];

    public function moveTo(float $x, float $y) : self
    {
        $path = clone $this;
        $path->operations[] = ['move-to', $x, $y];
        return $path;
    }

    public function lineTo(float $x, float $y) : self
    {
        $path = clone $this;
        $path->operations[] = ['line-to', $x, $y];
        return $path;
    }

    public function ellipticArc(
        float $xRadius,
        float $yRadius,
        float $xAxisRotation,
        bool $largeArc,
        bool $sweep,
        float $x,
        float $y
    ) : self {
        $path = clone $this;
        $path->operations[] = ['elliptic-arc', $xRadius, $yRadius, $xAxisRotation, $largeArc, $sweep, $x, $y];
        return $path;
    }

    public function close() : self
    {
        $path = clone $this;
        $path->operations[] = ['close'];
        return $path;
    }

    /**
     * @return array[]|Traversable
     */
    public function getIterator() : Traversable
    {
        foreach ($this->operations as $operation) {
            yield $operation;
        }
    }
}
