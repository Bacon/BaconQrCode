# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

BaconQrCode is a PHP QR code generator, ported from the ZXing library. It includes only the encoder (no decoder). The Reed-Solomon codec uses Phil Karn's implementation for performance.

## Commands

- **Install dependencies:** `composer install && npm install` (Node.js is needed for pixelmatch in image comparison tests)
- **Run all tests:** `vendor/bin/phpunit`
- **Run a single test:** `vendor/bin/phpunit --filter TestClassName::testMethodName`
- **Run tests in a directory:** `vendor/bin/phpunit test/Common/`
- **Lint:** `phpcs` (config in `phpcs.xml`)
- **PHP version:** requires ^8.1

## Architecture

The library has three main layers:

1. **Encoder** (`src/Encoder/`) - Core QR code generation
   - `Encoder` - Main entry point; encodes a string into a `QrCode` object using error correction, mode selection, and Reed-Solomon encoding
   - `QrCode` - Data object holding the encoded matrix, version, mode, and EC level
   - `ByteMatrix` - 2D byte grid representing the QR code modules
   - `MatrixUtil` - Builds the matrix (function patterns, data bits, masking)
   - `MaskUtil` - Mask pattern evaluation and selection

2. **Renderer** (`src/Renderer/`) - Converts a `QrCode` into an image or text
   - `RendererInterface` - Contract: takes a `QrCode`, returns a string
   - `ImageRenderer` - Composes a `RendererStyle` with an `ImageBackEndInterface` to render styled QR codes
   - `GDLibRenderer` - Separate renderer for GD (no gradients, no curves)
   - `PlainTextRenderer` - UTF-8 text output
   - **Image back ends** (`Renderer/Image/`): `ImagickImageBackEnd`, `SvgImageBackEnd`, `EpsImageBackEnd`
   - **Modules** (`Renderer/Module/`): Control data module shape (square, dots, rounded)
   - **Eyes** (`Renderer/Eye/`): Control position detection pattern shape
   - **Styling** (`Renderer/RendererStyle/`): Fill, gradients, colors, eye fills
   - **Path** (`Renderer/Path/`): Vector path primitives used by image back ends

3. **Common** (`src/Common/`) - Shared data structures and algorithms
   - `Version`, `ErrorCorrectionLevel`, `Mode`, `CharacterSetEci` - QR spec enums (using `dasprid/enum`)
   - `BitArray`, `BitMatrix` - Bit-level data structures
   - `ReedSolomonCodec` - Error correction codec
   - `FormatInformation` - Format/version info encoding

4. **Writer** (`src/Writer.php`) - Top-level facade; takes a `RendererInterface`, calls `Encoder::encode()`, then renders

## Coding Standards

- PSR-2 based with additional rules (see `phpcs.xml`): single quotes preferred, no short open tags, short array syntax, space after `!`
- Integration tests use snapshot assertions (`spatie/phpunit-snapshot-assertions`) and pixel comparison (`spatie/pixelmatch-php`)
