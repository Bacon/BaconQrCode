<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer;

use BaconQrCode\Encoder\MatrixUtil;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Exception;
use BaconQrCode\Renderer\Image\ImageBackendFactoryInterface;
use BaconQrCode\Renderer\Image\ImageBackendInterface;

final class ImageRenderer implements RendererInterface
{
    /**
     * @var RendererStyle
     */
    private $rendererStyle;

    /**
     * @var ImageBackendFactoryInterface
     */
    private $imageBackendFactory;

    public function __construct(RendererStyle $rendererStyle, ImageBackendFactoryInterface $imageBackendFactory)
    {
        $this->rendererStyle = $rendererStyle;
        $this->imageBackendFactory = $imageBackendFactory;
    }

    public function render(QrCode $qrCode) : string
    {
        $size = $this->rendererStyle->getSize();
        $margin = $this->rendererStyle->getMargin();
        $matrix = $qrCode->getMatrix();
        $matrixSize = $matrix->getWidth();

        if ($matrixSize !== $matrix->getHeight()) {
            throw new Exception\InvalidArgumentException('Matrix must have the same width and height');
        }

        $totalSize = $matrixSize + ($margin * 2);
        $moduleSize = $size / $totalSize;

        $imageBackend = $this->imageBackendFactory->__invoke($size, $this->rendererStyle->getBackgroundColor());
        $imageBackend->scale((float) $moduleSize);
        $imageBackend->translate((float) $margin, (float) $margin);

        $module = $this->rendererStyle->getModule();

        $this->drawEyes($imageBackend, $matrixSize);
        $moduleMatrix = clone $matrix;
        MatrixUtil::removePositionDetectionPatterns($moduleMatrix);
        $imageBackend->drawPath($module->createPath($moduleMatrix), $this->rendererStyle->getModuleColor());

        return $imageBackend->getBlob();
    }

    private function drawEyes(ImageBackendInterface $imageBackend, int $matrixSize) : void
    {
        $eye = $this->rendererStyle->getEye();

        $externalPath = $eye->getExternalPath();
        $internalPath = $eye->getInternalPath();
        $externalColor = $this->rendererStyle->getExternalEyeColor();
        $internalColor = $this->rendererStyle->getInternalEyeColor();

        $imageBackend->push();
        $imageBackend->translate(3.5, 3.5);
        $imageBackend->drawPath($externalPath, $externalColor);
        $imageBackend->drawPath($internalPath, $internalColor);
        $imageBackend->pop();

        $imageBackend->push();
        $imageBackend->translate($matrixSize - 3.5, 3.5);
        $imageBackend->rotate(90);
        $imageBackend->drawPath($externalPath, $externalColor);
        $imageBackend->drawPath($internalPath, $internalColor);
        $imageBackend->pop();

        $imageBackend->push();
        $imageBackend->translate(3.5, $matrixSize - 3.5);
        $imageBackend->rotate(-90);
        $imageBackend->drawPath($externalPath, $externalColor);
        $imageBackend->drawPath($internalPath, $internalColor);
        $imageBackend->pop();
    }
}
