<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use PHPUnit_Framework_TestCase as TestCase;

class EncoderTest extends TestCase
{
    public function testGetAlphanumericCode()
    {
        // The first ten code points are numbers.
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($i, Encoder::getAlphanumericCode(ord('0') + $i));
        }

        // The next 26 code points are capital alphabet letters.
        for ($i = 10; $i < 36; $i++) {
            // The first ten code points are numbers
            $this->assertEquals($i, Encoder::getAlphanumericCode(ord('A') + $i - 10));
        }

        // Others are symbol letters.
        $this->assertEquals(36, Encoder::getAlphanumericCode(' '));
        $this->assertEquals(37, Encoder::getAlphanumericCode('$'));
        $this->assertEquals(38, Encoder::getAlphanumericCode('%'));
        $this->assertEquals(39, Encoder::getAlphanumericCode('*'));
        $this->assertEquals(40, Encoder::getAlphanumericCode('+'));
        $this->assertEquals(41, Encoder::getAlphanumericCode('-'));
        $this->assertEquals(42, Encoder::getAlphanumericCode('.'));
        $this->assertEquals(43, Encoder::getAlphanumericCode('/'));
        $this->assertEquals(44, Encoder::getAlphanumericCode(':'));

        // Should return -1 for other letters.
        $this->assertEquals(-1, Encoder::getAlphanumericCode('a'));
        $this->assertEquals(-1, Encoder::getAlphanumericCode('#'));
        $this->assertEquals(-1, Encoder::getAlphanumericCode("\0"));
    }

    public function testChooseMode()
    {
        // Numeric mode
        $this->assertSame(Mode::NUMERIC, Encoder::chooseMode('0')->get());
        $this->assertSame(Mode::NUMERIC, Encoder::chooseMode('0123456789')->get());

        // Alphanumeric mode
        $this->assertSame(Mode::ALPHANUMERIC, Encoder::chooseMode('A')->get());
        $this->assertSame(Mode::ALPHANUMERIC, Encoder::chooseMode('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:')->get());

        // 8-bit byte mode
        $this->assertSame(Mode::BYTE, Encoder::chooseMode('a')->get());
        $this->assertSame(Mode::BYTE, Encoder::chooseMode('#')->get());
        $this->assertSame(Mode::BYTE, Encoder::chooseMode('')->get());

        // AIUE in Hiragana in SHIFT-JIS
        $this->assertSame(Mode::BYTE, Encoder::chooseMode("\x8\xa\x8\xa\x8\xa\x8\xa6")->get());

        // Nihon in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, Encoder::chooseMode("\x9\xf\x9\x7b")->get());

        // Sou-Utso-Byou in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, Encoder::chooseMode("\xe\x4\x9\x5\x9\x61")->get());
    }

    public function testEncode()
    {
        $qrCode = Encoder::encode('ABCDEF', new ErrorCorrectionLevel(ErrorCorrectionLevel::H));
        $expected = "<<\n"
                  . " mode: ALPHANUMERIC\n"
                  . " ecLevel: H\n"
                  . " version: 1\n"
                  . " maskPattern: 0\n"
                  . " matrix:\n"
                  . " 1 1 1 1 1 1 1 0 1 1 1 1 0 0 1 1 1 1 1 1 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 1 1 0 0 1 0 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 0 1 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 1 1 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 1 0 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 0 0 0 0 1 0 0 0 0 0 1\n"
                  . " 1 1 1 1 1 1 1 0 1 0 1 0 1 0 1 1 1 1 1 1 1\n"
                  . " 0 0 0 0 0 0 0 0 0 0 1 0 1 0 0 0 0 0 0 0 0\n"
                  . " 0 0 1 0 1 1 1 0 1 1 0 0 1 1 0 0 0 1 0 0 1\n"
                  . " 1 0 1 1 1 0 0 1 0 0 0 1 0 1 0 0 0 0 0 0 0\n"
                  . " 0 0 1 1 0 0 1 0 1 0 0 0 1 0 1 0 1 0 1 1 0\n"
                  . " 1 1 0 1 0 1 0 1 1 1 0 1 0 1 0 0 0 0 0 1 0\n"
                  . " 0 0 1 1 0 1 1 1 1 0 0 0 1 0 1 0 1 1 1 1 0\n"
                  . " 0 0 0 0 0 0 0 0 1 0 0 1 1 1 0 1 0 1 0 0 0\n"
                  . " 1 1 1 1 1 1 1 0 0 0 1 0 1 0 1 1 0 0 0 0 1\n"
                  . " 1 0 0 0 0 0 1 0 1 1 1 1 0 1 0 1 1 1 1 0 1\n"
                  . " 1 0 1 1 1 0 1 0 1 0 1 1 0 1 0 1 0 0 0 0 1\n"
                  . " 1 0 1 1 1 0 1 0 0 1 1 0 1 1 1 1 0 1 0 1 0\n"
                  . " 1 0 1 1 1 0 1 0 1 0 0 0 1 0 1 0 1 1 1 0 1\n"
                  . " 1 0 0 0 0 0 1 0 0 1 1 0 1 1 0 1 0 0 0 1 1\n"
                  . " 1 1 1 1 1 1 1 0 0 0 0 0 0 0 0 0 1 0 1 0 1\n"
                  . ">>\n";

        $this->assertEquals($expected, $qrCode->__toString());
    }
}