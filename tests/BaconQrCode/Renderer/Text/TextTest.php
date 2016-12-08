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
use BaconQrCode\Renderer\Text\Plain;
use BaconQrCode\Writer;
use PHPUnit_Framework_TestCase as TestCase;

class PlainTest extends TestCase
{
    /**
     * @var Plain
     */
    protected $renderer;

    /**
     * @var Writer
     */
    protected $writer;

    public function setUp()
    {
        $this->renderer = new Plain();
        $this->writer = new Writer($this->renderer);
    }

    public function testBasicRender()
    {
        $content = 'foobar';
        $expected =
            "                       \n" .
            " ███████ █████ ███████ \n" .
            " █     █  █ █  █     █ \n" .
            " █ ███ █  ██   █ ███ █ \n" .
            " █ ███ █  ███  █ ███ █ \n" .
            " █ ███ █   █ █ █ ███ █ \n" .
            " █     █    ██ █     █ \n" .
            " ███████ █ █ █ ███████ \n" .
            "         █████         \n" .
            " ██ ██ █  ██ █ █     █ \n" .
            "    ██    ██ █ █ ██    \n" .
            "  ████████ █  ██ █  ██ \n" .
            "           ██      █ █ \n" .
            "  ██  ███  █   █  █  █ \n" .
            "         █ ███    █ █  \n" .
            " ███████  ██ ██████    \n" .
            " █     █   ████   ██   \n" .
            " █ ███ █ ██ ██ ██ █ ██ \n" .
            " █ ███ █ ██ ██  █ ██   \n" .
            " █ ███ █   █   █ ██ ██ \n" .
            " █     █ ███  ███ ████ \n" .
            " ███████ ████   ██     \n" .
            "                       \n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testBaseCompactRender()
    {
        $content = 'foobar';
        $expected =
            "                       \n" .
            " █▀▀▀▀▀█ ▀█▀█▀ █▀▀▀▀▀█ \n" .
            " █ ███ █  ██▄  █ ███ █ \n" .
            " █ ▀▀▀ █   ▀▄█ █ ▀▀▀ █ \n" .
            " ▀▀▀▀▀▀▀ █▄█▄█ ▀▀▀▀▀▀▀ \n" .
            " ▀▀ ██ ▀  ██ █ █ ▄▄  ▀ \n" .
            "  ▀▀▀▀▀▀▀▀ █▄ ▀▀ ▀ ▄▀█ \n" .
            "  ▀▀  ▀▀▀▄ █▄▄ ▀  █ ▄▀ \n" .
            " █▀▀▀▀▀█  ▀█▄██▀▀▀█▄   \n" .
            " █ ███ █ ██ ██ ▀█ █▄▀▀ \n" .
            " █ ▀▀▀ █ ▄▄█  ▄█▄▀█▄██ \n" .
            " ▀▀▀▀▀▀▀ ▀▀▀▀   ▀▀     \n" .
            "                       \n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setCompact(true);
        $this->assertEquals(true, $this->renderer->getCompact());
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testBasicRenderNoMargins()
    {
        $content = 'foobar';
        $expected =
            "███████ █████ ███████\n" .
            "█     █  █ █  █     █\n" .
            "█ ███ █  ██   █ ███ █\n" .
            "█ ███ █  ███  █ ███ █\n" .
            "█ ███ █   █ █ █ ███ █\n" .
            "█     █    ██ █     █\n" .
            "███████ █ █ █ ███████\n" .
            "        █████        \n" .
            "██ ██ █  ██ █ █     █\n" .
            "   ██    ██ █ █ ██   \n" .
            " ████████ █  ██ █  ██\n" .
            "          ██      █ █\n" .
            " ██  ███  █   █  █  █\n" .
            "        █ ███    █ █ \n" .
            "███████  ██ ██████   \n" .
            "█     █   ████   ██  \n" .
            "█ ███ █ ██ ██ ██ █ ██\n" .
            "█ ███ █ ██ ██  █ ██  \n" .
            "█ ███ █   █   █ ██ ██\n" .
            "█     █ ███  ███ ████\n" .
            "███████ ████   ██    \n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setMargin(0);
        $this->assertEquals(0, $this->renderer->getMargin());
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testBaseCompactRenderNoMargins()
    {
        $content = 'foobar';
        $expected =
            "█▀▀▀▀▀█ ▀█▀█▀ █▀▀▀▀▀█\n" .
            "█ ███ █  ██▄  █ ███ █\n" .
            "█ ▀▀▀ █   ▀▄█ █ ▀▀▀ █\n" .
            "▀▀▀▀▀▀▀ █▄█▄█ ▀▀▀▀▀▀▀\n" .
            "▀▀ ██ ▀  ██ █ █ ▄▄  ▀\n" .
            " ▀▀▀▀▀▀▀▀ █▄ ▀▀ ▀ ▄▀█\n" .
            " ▀▀  ▀▀▀▄ █▄▄ ▀  █ ▄▀\n" .
            "█▀▀▀▀▀█  ▀█▄██▀▀▀█▄  \n" .
            "█ ███ █ ██ ██ ▀█ █▄▀▀\n" .
            "█ ▀▀▀ █ ▄▄█  ▄█▄▀█▄██\n" .
            "▀▀▀▀▀▀▀ ▀▀▀▀   ▀▀    \n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setMargin(0);
        $this->renderer->setCompact(true);
        $this->assertEquals(0, $this->renderer->getMargin());
        $this->assertEquals(true, $this->renderer->getCompact());
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testBasicRenderCustomChar()
    {
        $content = 'foobar';
        $expected =
            "-----------------------\n" .
            "-#######-#####-#######-\n" .
            "-#-----#--#-#--#-----#-\n" .
            "-#-###-#--##---#-###-#-\n" .
            "-#-###-#--###--#-###-#-\n" .
            "-#-###-#---#-#-#-###-#-\n" .
            "-#-----#----##-#-----#-\n" .
            "-#######-#-#-#-#######-\n" .
            "---------#####---------\n" .
            "-##-##-#--##-#-#-----#-\n" .
            "----##----##-#-#-##----\n" .
            "--########-#--##-#--##-\n" .
            "-----------##------#-#-\n" .
            "--##--###--#---#--#--#-\n" .
            "---------#-###----#-#--\n" .
            "-#######--##-######----\n" .
            "-#-----#---####---##---\n" .
            "-#-###-#-##-##-##-#-##-\n" .
            "-#-###-#-##-##--#-##---\n" .
            "-#-###-#---#---#-##-##-\n" .
            "-#-----#-###--###-####-\n" .
            "-#######-####---##-----\n" .
            "-----------------------\n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setFullBlock('#');
        $this->renderer->setEmptyBlock('-');
        $this->assertEquals('#', $this->renderer->getFullBlock());
        $this->assertEquals('-', $this->renderer->getEmptyBlock());
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testBaseCompactRenderCustomChar()
    {
        $content = 'foobar';
        $expected =
            ".......................\n" .
            ".#¯¯¯¯¯#.¯#¯#¯.#¯¯¯¯¯#.\n" .
            ".#.###.#..##_..#.###.#.\n" .
            ".#.¯¯¯.#...¯_#.#.¯¯¯.#.\n" .
            ".¯¯¯¯¯¯¯.#_#_#.¯¯¯¯¯¯¯.\n" .
            ".¯¯.##.¯..##.#.#.__..¯.\n" .
            "..¯¯¯¯¯¯¯¯.#_.¯¯.¯._¯#.\n" .
            "..¯¯..¯¯¯_.#__.¯..#._¯.\n" .
            ".#¯¯¯¯¯#..¯#_##¯¯¯#_...\n" .
            ".#.###.#.##.##.¯#.#_¯¯.\n" .
            ".#.¯¯¯.#.__#.._#_¯#_##.\n" .
            ".¯¯¯¯¯¯¯.¯¯¯¯...¯¯.....\n" .
            ".......................\n"
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setCompact(true);
        $this->renderer->setFullBlock('#');
        $this->renderer->setEmptyBlock('.');
        $this->renderer->setUpperHalfBlock('¯');
        $this->renderer->setLowerHalfBlock('_');
        $this->assertEquals(true, $this->renderer->getCompact());
        $this->assertEquals('#', $this->renderer->getFullBlock());
        $this->assertEquals('.', $this->renderer->getEmptyBlock());
        $this->assertEquals('¯', $this->renderer->getUpperHalfBlock());
        $this->assertEquals('_', $this->renderer->getLowerHalfBlock());
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }
}
