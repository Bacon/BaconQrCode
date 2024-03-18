<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Path;

final class Move implements OperationInterface
{
    public function __construct(private readonly float $x, private readonly float $y)
    {
    }

    public function getX() : float
    {
        return $this->x;
    }

    public function getY() : float
    {
        return $this->y;
    }

    /**
     * @return self
     */
    public function translate(float $x, float $y) : OperationInterface
    {
        return new self($this->x + $x, $this->y + $y);
    }
}
