# QR Code generator

Master: [![Build Status](https://api.travis-ci.org/Bacon/BaconQrCode.png?branch=master)](http://travis-ci.org/Bacon/BaconQrCode)

## Introduction
BaconQrCode is a port of QR code portion of the ZXing library. It currently
only features the encoder part, but could later receive the decoder part as
well.

As the Reed Solomon codec implementation of the ZXing library performs quite
slow in PHP, it was exchanged with the implementation by Phil Karn.


## Example usage
```php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackendBackendFactory;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    RendererStyle::default(400),
    new ImagickImageBackendBackendFactory()
);
$writer = new Writer($renderer);
$writer->writeFile('Hello World!', 'qrcode.png');
```

## Available image renderer backends
BaconQrCode comes with multiple backends for rendering images. Currently included are the following:

- `ImagickImageBackendBackend`: renders raster images using the Imagick library
- `SvgImageBackendBackend`: renders SVG files using XMLWriter
