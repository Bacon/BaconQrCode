QR Code generator
=================

Master: [![Build Status](https://api.travis-ci.org/Bacon/BaconQrCode.png?branch=master)](http://travis-ci.org/Bacon/BaconQrCode)

Introduction
------------
BaconQrCode is a port of QR code portion of the ZXing library. It currently
only features the encoder part, but could later receive the decoder part as
well.

As the Reed Solomon codec implementation of the ZXing library performs quite
slow in PHP, it was exchanged with the implementation by Phil Karn.


Example usage
------------
``` PHP
$render = new \BaconQrCode\Renderer\Image\Png();
$render->setHeight(256);
$render->setWidth(256);
$QRCode = new \BaconQrCode\Writer($render);
$fileName = tempnam(sys_get_temp_dir(), 'QRCode');
$QRCode->writeFile('Hello World!', $fileName);
```
