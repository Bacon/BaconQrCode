<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
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

        foreach ($path as $operation) {
            switch ($operation[0]) {
                case 'move-to':
                    $pathData[] = sprintf('M%s %s', (string) $operation[1], (string) $operation[2]);
                    break;

                case 'line-to':
                    $pathData[] = sprintf('L%s %s', (string) $operation[1], (string) $operation[2]);
                    break;

                case 'elliptic-arc':
                    $pathData[] = sprintf(
                        'A%s %s %s %d %d %s %s',
                        (string) $operation[1],
                        (string) $operation[2],
                        (string) $operation[3],
                        (int) $operation[4],
                        (int) $operation[5],
                        (string) $operation[6],
                        (string) $operation[7]
                    );
                    break;

                case 'close':
                    $pathData[] = 'Z';
                    break;
            }
        }

        $this->xmlWriter->startElement('path');
        $this->xmlWriter->writeAttribute('fill', $this->getColorString($color));
        $this->xmlWriter->writeAttribute('fill-rule', 'evenodd');
        $this->xmlWriter->writeAttribute('d', implode(' ', $pathData));

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
