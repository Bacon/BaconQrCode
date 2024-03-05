<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Path;

final class Curve implements OperationInterface
{
    public function __construct(
        private readonly float $x1,
        private readonly float $y1,
        private readonly float $x2,
        private readonly float $y2,
        private readonly float $x3,
        private readonly float $y3
    ) {
    }

    public function getX1() : float
    {
        return $this->x1;
    }

    public function getY1() : float
    {
        return $this->y1;
    }

    public function getX2() : float
    {
        return $this->x2;
    }

    public function getY2() : float
    {
        return $this->y2;
    }

    public function getX3() : float
    {
        return $this->x3;
    }

    public function getY3() : float
    {
        return $this->y3;
    }

    /**
     * @return self
     */
    public function translate(float $x, float $y) : OperationInterface
    {
        return new self(
            $this->x1 + $x,
            $this->y1 + $y,
            $this->x2 + $x,
            $this->y2 + $y,
            $this->x3 + $x,
            $this->y3 + $y
        );
    }
}
