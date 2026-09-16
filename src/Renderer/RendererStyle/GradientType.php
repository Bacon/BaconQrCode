<?php
declare(strict_types = 1);

namespace BaconQrCode\Renderer\RendererStyle;

enum GradientType
{
    case VERTICAL;
    case HORIZONTAL;
    case DIAGONAL;
    case INVERSE_DIAGONAL;
    case RADIAL;
}
