<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Exception\RuntimeException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Path\Close;
use BaconQrCode\Renderer\Path\Curve;
use BaconQrCode\Renderer\Path\EllipticArc;
use BaconQrCode\Renderer\Path\Line;
use BaconQrCode\Renderer\Path\Move;
use BaconQrCode\Renderer\Path\Path;
use XMLWriter;

final class SvgImageBackendBackend implements ImageBackendInterface
{
    /**
     * @var XMLWriter
     */
    private $xmlWriter;

    /**
     * @var int[]
     */
    private $stack = [0];

    /**
     * @var int
     */
    private $currentStack;

    public function __construct(int $size, ColorInterface $backgroundColor)
    {
        $this->xmlWriter = new XMLWriter();
        $this->xmlWriter->openMemory();

        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('svg');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->xmlWriter->writeAttribute('version', '1.1');
        $this->xmlWriter->writeAttribute('width', $size . 'px');
        $this->xmlWriter->writeAttribute('height', $size . 'px');
        $this->xmlWriter->writeAttribute('viewBox', '0 0 '. $size . ' ' . $size);

        $this->currentStack = 0;
        ++$this->stack[$this->currentStack];

        $alpha = 1;

        if ($backgroundColor instanceof Alpha) {
            $alpha = $backgroundColor->getAlpha() / 100;
        }

        if (0 === $alpha) {
            return;
        }

        $this->xmlWriter->startElement('rect');
        $this->xmlWriter->writeAttribute('x', '0');
        $this->xmlWriter->writeAttribute('y', '0');
        $this->xmlWriter->writeAttribute('width', (string) $size);
        $this->xmlWriter->writeAttribute('height', (string) $size);
        $this->xmlWriter->writeAttribute('fill', $this->getColorString($backgroundColor));

        if ($alpha < 1) {
            $this->xmlWriter->writeAttribute('fill-opacity', (string) $alpha);
        }

        $this->xmlWriter->endElement();
    }

    public function scale(float $size) : void
    {
        $this->xmlWriter->startElement('g');
        $this->xmlWriter->writeAttribute('transform', 'scale(' . $size . ')');
        ++$this->stack[$this->currentStack];
    }

    public function translate(float $x, float $y) : void
    {
        $this->xmlWriter->startElement('g');
        $this->xmlWriter->writeAttribute('transform', 'translate(' . $x . ',' . $y . ')');
        ++$this->stack[$this->currentStack];
    }

    public function rotate(int $degrees) : void
    {
        $this->xmlWriter->startElement('g');
        $this->xmlWriter->writeAttribute('transform', 'rotate(' . $degrees . ')');
        ++$this->stack[$this->currentStack];
    }

    public function push() : void
    {
        $this->xmlWriter->startElement('g');
        $this->stack[] = 1;
        ++$this->currentStack;
    }

    public function pop() : void
    {
        for ($i = 0; $i < $this->stack[$this->currentStack]; ++$i) {
            $this->xmlWriter->endElement();
        }

        array_pop($this->stack);
        --$this->currentStack;
    }

    public function drawPath(Path $path, ColorInterface $color) : void
    {
        $alpha = 1;

        if ($color instanceof Alpha) {
            $alpha = $color->getAlpha() / 100;
        }

        if (0 === $alpha) {
            return;
        }

        $pathData = [];

        foreach ($path as $op) {
            switch (true) {
                case $op instanceof Move:
                    $pathData[] = sprintf('M%s %s', (string) $op->getX(), (string) $op->getY());
                    break;

                case $op instanceof Line:
                    $pathData[] = sprintf('L%s %s', (string) $op->getX(), (string) $op->getY());
                    break;

                case $op instanceof EllipticArc:
                    $pathData[] = sprintf(
                        'A%s %s %s %u %u %s %s',
                        (string) $op->getXRadius(),
                        (string) $op->getYRadius(),
                        (string) $op->getXAxisAngle(),
                        $op->isLargeArc(),
                        $op->isSweep(),
                        (string) $op->getX(),
                        (string) $op->getY()
                    );
                    break;

                case $op instanceof Curve:
                    $pathData[] = sprintf(
                        'C%s %s %s %s %s %s',
                        (string) $op->getX1(),
                        (string) $op->getY1(),
                        (string) $op->getX2(),
                        (string) $op->getY2(),
                        (string) $op->getX3(),
                        (string) $op->getY3()
                    );
                    break;

                case $op instanceof Close:
                    $pathData[] = 'Z';
                    break;

                default:
                    throw new RuntimeException('Unexpected draw operation: ' . get_class($op));
            }
        }

        $this->xmlWriter->startElement('path');
        $this->xmlWriter->writeAttribute('fill', $this->getColorString($color));
        $this->xmlWriter->writeAttribute('fill-rule', 'evenodd');
        $this->xmlWriter->writeAttribute('d', implode('', $pathData));

        if ($alpha < 1) {
            $this->xmlWriter->writeAttribute('fill-opacity', (string) $alpha);
        }

        $this->xmlWriter->endElement();
    }

    public function getBlob() : string
    {
        foreach ($this->stack as $openElements) {
            for ($i = $openElements; $i > 0; --$i) {
                $this->xmlWriter->endElement();
            }
        }

        $this->xmlWriter->endDocument();
        return $this->xmlWriter->outputMemory(true);
    }

    private function getColorString(ColorInterface $color) : string
    {
        $color = $color->toRgb();

        return sprintf(
            '#%02x%02x%02x',
            $color->getRed(),
            $color->getGreen(),
            $color->getBlue()
        );
    }
}
