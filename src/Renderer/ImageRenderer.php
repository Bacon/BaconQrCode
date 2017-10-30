<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\MatrixUtil;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception\InvalidArgumentException;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;

final class ImageRenderer implements RendererInterface
{
    /**
     * @var RendererStyle
     */
    private $rendererStyle;

    /**
     * @var ImageBackEndInterface
     */
    private $imageBackEnd;

    public function __construct(RendererStyle $rendererStyle, ImageBackEndInterface $imageBackEnd)
    {
        $this->rendererStyle = $rendererStyle;
        $this->imageBackEnd = $imageBackEnd;
    }

    /**
     * @throws InvalidArgumentException if matrix width doesn't match height
     */
    public function render(QrCode $qrCode) : string
    {
        $size = $this->rendererStyle->getSize();
        $margin = $this->rendererStyle->getMargin();
        $matrix = $qrCode->getMatrix();
        $matrixSize = $matrix->getWidth();

        if ($matrixSize !== $matrix->getHeight()) {
            throw new InvalidArgumentException('Matrix must have the same width and height');
        }

        $totalSize = $matrixSize + ($margin * 2);
        $moduleSize = $size / $totalSize;

        $this->imageBackEnd->new($size, $this->rendererStyle->getBackgroundColor());
        $this->imageBackEnd->scale((float) $moduleSize);
        $this->imageBackEnd->translate((float) $margin, (float) $margin);

        $module = $this->rendererStyle->getModule();
        $this->drawEyes($matrixSize);
        $moduleMatrix = clone $matrix;
        MatrixUtil::removePositionDetectionPatterns($moduleMatrix);
        $this->imageBackEnd->drawPath($module->createPath($moduleMatrix), $this->rendererStyle->getModuleColor());

        return $this->imageBackEnd->done();
    }

    private function drawEyes(int $matrixSize) : void
    {
        $eye = $this->rendererStyle->getEye();

        $externalPath = $eye->getExternalPath();
        $internalPath = $eye->getInternalPath();
        $externalColor = $this->rendererStyle->getExternalEyeColor();
        $internalColor = $this->rendererStyle->getInternalEyeColor();

        $this->imageBackEnd->push();
        $this->imageBackEnd->translate(3.5, 3.5);
        $this->imageBackEnd->drawPath($externalPath, $externalColor);
        $this->imageBackEnd->drawPath($internalPath, $internalColor);
        $this->imageBackEnd->pop();

        $this->imageBackEnd->push();
        $this->imageBackEnd->translate($matrixSize - 3.5, 3.5);
        $this->imageBackEnd->rotate(90);
        $this->imageBackEnd->drawPath($externalPath, $externalColor);
        $this->imageBackEnd->drawPath($internalPath, $internalColor);
        $this->imageBackEnd->pop();

        $this->imageBackEnd->push();
        $this->imageBackEnd->translate(3.5, $matrixSize - 3.5);
        $this->imageBackEnd->rotate(-90);
        $this->imageBackEnd->drawPath($externalPath, $externalColor);
        $this->imageBackEnd->drawPath($internalPath, $internalColor);
        $this->imageBackEnd->pop();
    }
}
