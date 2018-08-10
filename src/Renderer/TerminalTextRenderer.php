<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception\InvalidArgumentException;

final class TerminalTextRenderer implements RendererInterface
{
    /**
     * Filled
     */
    private const FILLED_BLOCK = "\033[47m  \033[0m";

    /**
     * Unfilled
     */
    private const UNFILLED_BLOCK = "\033[40m  \033[0m";

    /**
     * @var int
     */
    private $margin;

    public function __construct(int $margin = 2)
    {
        $this->margin = $margin;
    }

    /**
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrCode) : string
    {
        $matrix = $qrCode->getMatrix();
        $matrixSize = $matrix->getWidth();

        if ($matrixSize !== $matrix->getHeight()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $rows = $matrix->getArray()->toArray();

        if (0 !== $matrixSize % 2) {
            $rows[] = array_fill(0, $matrixSize, 0);
        }

        $horizontalMargin = str_repeat(self::UNFILLED_BLOCK, $this->margin);
        $result = str_repeat("\n", (int) ceil($this->margin / 2));

        for ($i = 0; $i < $matrixSize; $i += 1) {
            $result .= $horizontalMargin;

            for ($j = 0; $j < $matrixSize; ++$j) {
                $result .= $rows[$i][$j] ? self::FILLED_BLOCK : self::UNFILLED_BLOCK;
            }

            $result .= $horizontalMargin . "\n";
        }

        $result .= str_repeat("\n", (int) ceil($this->margin / 2));

        return $result;
    }
}
