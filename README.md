# QR Code generator

[![PHP CI](https://github.com/Bacon/BaconQrCode/actions/workflows/ci.yml/badge.svg)](https://github.com/Bacon/BaconQrCode/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/Bacon/BaconQrCode/branch/master/graph/badge.svg?token=rD0HcAiEEx)](https://codecov.io/gh/Bacon/BaconQrCode)
[![Latest Stable Version](https://poser.pugx.org/bacon/bacon-qr-code/v/stable)](https://packagist.org/packages/bacon/bacon-qr-code)
[![Total Downloads](https://poser.pugx.org/bacon/bacon-qr-code/downloads)](https://packagist.org/packages/bacon/bacon-qr-code)
[![License](https://poser.pugx.org/bacon/bacon-qr-code/license)](https://packagist.org/packages/bacon/bacon-qr-code)


## Introduction
BaconQrCode is a port of QR code portion of the ZXing library. It currently
only features the encoder part, but could later receive the decoder part as
well.

As the Reed Solomon codec implementation of the ZXing library performs quite
slow in PHP, it was exchanged with the implementation by Phil Karn.


## Example usage
```php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(400),
    new ImagickImageBackEnd()
);
$writer = new Writer($renderer);
$writer->writeFile('Hello World!', 'qrcode.png');
```

## Available image renderer back ends
BaconQrCode comes with multiple back ends for rendering images. Currently included are the following:

- `ImagickImageBackEnd`: renders raster images using the Imagick library
- `SvgImageBackEnd`: renders SVG files using XMLWriter
- `EpsImageBackEnd`: renders EPS files

### GDLib Renderer
GD library has so many limitations, that GD support is not added as backend, but as separated renderer.
Use `GDLibRenderer` instead of `ImageRenderer`. These are the limitations:

- Does not support gradient.
- Does not support any curves, so you QR code is always squared.

Example usage:

```php
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

$renderer = new GDLibRenderer(400);
$writer = new Writer($renderer);
$writer->writeFile('Hello World!', 'qrcode.png');
```

## Known issues

### ImagickImageBackEnd: white pixel artifacts

When using `ImagickImageBackEnd`, single white pixels may appear inside filled regions. This is
most visible with margin 0 (where artifacts appear at the image edge), but can in theory occur at
any position. The cause is a bug in ImageMagick's path fill rasterizer (`GetFillAlpha` in
`MagickCore/draw.c`): an off-by-one error in the winding number calculation combined with an edge
skipping bug in the scanline processing can incorrectly classify pixels as outside the polygon.

The bug cannot be reliably worked around in this library:

- **Canvas padding** (rendering on a larger canvas and cropping) does not work because the required
  padding depends on the scale factor, path complexity, and ImageMagick's internal edge processing
  state. No fixed padding value is safe for all inputs.
- **Post-processing** (scanning for and fixing isolated white pixels) risks corrupting legitimate
  rendering features such as curved module edges.

For artifact-free output, use `SvgImageBackEnd` or `GDLibRenderer` instead.

## Development

To run unit tests, you need to have [Node.js](https://nodejs.org/en) and the pixelmatch library installed. Running
`npm install` will install this for you.
