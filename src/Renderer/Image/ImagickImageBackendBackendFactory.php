<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\ColorInterface;

final class ImagickImageBackendBackendFactory implements ImageBackendFactoryInterface
{
    /**
     * @var string
     */
    private $imageFormat;

    /**
     * @var int
     */
    private $compressionQuality;

    public function __construct(string $imageFormat = 'png', int $compressionQuality = 100)
    {
        $this->imageFormat = $imageFormat;
        $this->compressionQuality = $compressionQuality;
    }

    public function __invoke(int $size, ColorInterface $backgroundColor) : ImageBackendInterface
    {
        return new ImagickImageBackendBackend($size, $backgroundColor, $this->imageFormat, $this->compressionQuality);
    }
}
