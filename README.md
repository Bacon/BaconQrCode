# QR Code generator

[![Build Status](https://api.travis-ci.org/Bacon/BaconQrCode.png?branch=master)](http://travis-ci.org/Bacon/BaconQrCode)
[![Coverage Status](https://coveralls.io/repos/github/Bacon/BaconQrCode/badge.svg?branch=master)](https://coveralls.io/github/Bacon/BaconQrCode?branch=master)
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
require_once("<Filepath>/vendor/autoload.php"); //include all files in the downloaded otphp package
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

## Example to create an SVG in current file

```php
require_once("<Filepath>/vendor/autoload.php"); //include all files in the downloaded otphp package

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(400),
    new SvgImageBackEnd()
);
$writer = new Writer($renderer);

$qr_image = $writer->writeString($stringToMakeImage);
echo "$qr_image";

```

## Available image renderer back ends
BaconQrCode comes with multiple back ends for rendering images. Currently included are the following:

- `ImagickImageBackEnd`: renders raster images using the Imagick library
- `SvgImageBackEnd`: renders SVG files using XMLWriter
- `EpsImageBackEnd`: renders EPS files
