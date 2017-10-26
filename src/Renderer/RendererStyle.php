<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer;

use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Eye\SquareEye;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\Module\SquareModule;

final class RendererStyle
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $margin;

    /**
     * @var EyeInterface
     */
    private $eye;

    /**
     * @var ModuleInterface
     */
    private $module;

    /**
     * @var ColorInterface
     */
    private $externalEyeColor;

    /**
     * @var ColorInterface
     */
    private $internalEyeColor;

    /**
     * @var ColorInterface
     */
    private $moduleColor;

    /**
     * @var ColorInterface
     */
    private $backgroundColor;

    public function __construct(
        int $size,
        int $margin,
        EyeInterface $eye,
        ModuleInterface $module,
        ColorInterface $externalEyeColor,
        ColorInterface $internalEyeColor,
        ColorInterface $moduleColor,
        ColorInterface $backgroundColor
    ) {
        var_dump($module);
        $this->size = $size;
        $this->eye = $eye;
        $this->module = $module;
        $this->externalEyeColor = $externalEyeColor;
        $this->internalEyeColor = $internalEyeColor;
        $this->moduleColor = $moduleColor;
        $this->backgroundColor = $backgroundColor;
        $this->margin = $margin;
    }

    public static function default(int $size, int $margin = 4) : self
    {
        return self::uniformColor(
            $size,
            new Rgb(0, 0, 0),
            new Rgb(255, 255, 255),
            null,
            null,
            $margin
        );
    }

    public static function uniformColor(
        int $size,
        ColorInterface $foregroundColor,
        ColorInterface $backgroundColor,
        ?EyeInterface $eye = null,
        ?ModuleInterface $module = null,
        int $margin = 4
    ) : self {
        return new self(
            $size,
            $margin,
            $eye ?: new SquareEye(),
            $module ?: new SquareModule(),
            $foregroundColor,
            $foregroundColor,
            $foregroundColor,
            $backgroundColor
        );
    }

    public function withSize(int $size) : self
    {
        $style = clone $this;
        $style->size = $size;
        return $style;
    }

    public function withMargin(int $margin) : self
    {
        $style = clone $this;
        $style->margin = $margin;
        return $style;
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function getMargin() : int
    {
        return $this->margin;
    }

    public function getEye() : EyeInterface
    {
        return $this->eye;
    }

    public function getModule() : ModuleInterface
    {
        return $this->module;
    }

    public function getExternalEyeColor() : ColorInterface
    {
        return $this->externalEyeColor;
    }

    public function getInternalEyeColor() : ColorInterface
    {
        return $this->internalEyeColor;
    }

    public function getModuleColor() : ColorInterface
    {
        return $this->moduleColor;
    }

    public function getBackgroundColor() : ColorInterface
    {
        return $this->backgroundColor;
    }
}
