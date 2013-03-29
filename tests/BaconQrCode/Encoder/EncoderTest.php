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
use ReflectionClass;
use ReflectionMethod;

class EncoderTest extends TestCase
{
    protected $methods = array();

    public function setUp()
    {
        // Hack to be able to test protected methods
        $reflection = new ReflectionClass('BaconQrCode\Encoder\Encoder');

        foreach ($reflection->getMethods(ReflectionMethod::IS_STATIC) as $method) {
            $method->setAccessible(true);
            $this->methods[$method->getName()] = $method;
        }
    }

    public function testGetAlphanumericCode()
    {
        // The first ten code points are numbers.
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($i, $this->methods['getAlphanumericCode']->invoke(null, ord('0') + $i));
        }

        // The next 26 code points are capital alphabet letters.
        for ($i = 10; $i < 36; $i++) {
            // The first ten code points are numbers
            $this->assertEquals($i, $this->methods['getAlphanumericCode']->invoke(null, ord('A') + $i - 10));
        }

        // Others are symbol letters.
        $this->assertEquals(36, $this->methods['getAlphanumericCode']->invoke(null, ' '));
        $this->assertEquals(37, $this->methods['getAlphanumericCode']->invoke(null, '$'));
        $this->assertEquals(38, $this->methods['getAlphanumericCode']->invoke(null, '%'));
        $this->assertEquals(39, $this->methods['getAlphanumericCode']->invoke(null, '*'));
        $this->assertEquals(40, $this->methods['getAlphanumericCode']->invoke(null, '+'));
        $this->assertEquals(41, $this->methods['getAlphanumericCode']->invoke(null, '-'));
        $this->assertEquals(42, $this->methods['getAlphanumericCode']->invoke(null, '.'));
        $this->assertEquals(43, $this->methods['getAlphanumericCode']->invoke(null, '/'));
        $this->assertEquals(44, $this->methods['getAlphanumericCode']->invoke(null, ':'));

        // Should return -1 for other letters.
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, 'a'));
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, '#'));
        $this->assertEquals(-1, $this->methods['getAlphanumericCode']->invoke(null, "\0"));
    }

    public function testChooseMode()
    {
        // Numeric mode
        $this->assertSame(Mode::NUMERIC, $this->methods['chooseMode']->invoke(null, '0')->get());
        $this->assertSame(Mode::NUMERIC, $this->methods['chooseMode']->invoke(null, '0123456789')->get());

        // Alphanumeric mode
        $this->assertSame(Mode::ALPHANUMERIC, $this->methods['chooseMode']->invoke(null, 'A')->get());
        $this->assertSame(Mode::ALPHANUMERIC, $this->methods['chooseMode']->invoke(null, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:')->get());

        // 8-bit byte mode
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, 'a')->get());
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, '#')->get());
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, '')->get());

        // AIUE in Hiragana in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\x8\xa\x8\xa\x8\xa\x8\xa6")->get());

        // Nihon in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\x9\xf\x9\x7b")->get());

        // Sou-Utso-Byou in Kanji in SHIFT-JIS
        $this->assertSame(Mode::BYTE, $this->methods['chooseMode']->invoke(null, "\xe\x4\x9\x5\x9\x61")->get());
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