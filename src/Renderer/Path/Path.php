<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Path;

use IteratorAggregate;
use Traversable;

final class Path implements IteratorAggregate
{
    /**
     * @var object[]
     */
    private $operations = [];

    public function moveTo(float $x, float $y) : self
    {
        $path = clone $this;
        $path->operations[] = new Move($x, $y);
        return $path;
    }

    public function lineTo(float $x, float $y) : self
    {
        $path = clone $this;
        $path->operations[] = new Line($x, $y);
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
        $path->operations[] = new EllipticArc($xRadius, $yRadius, $xAxisRotation, $largeArc, $sweep, $x, $y);
        return $path;
    }

    public function close() : self
    {
        $path = clone $this;
        $path->operations[] = Close::instance();
        return $path;
    }

    /**
     * @return object[]|Traversable
     */
    public function getIterator() : Traversable
    {
        foreach ($this->operations as $operation) {
            yield $operation;
        }
    }
}
