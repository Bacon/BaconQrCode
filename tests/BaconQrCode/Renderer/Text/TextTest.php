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

    public function additionProvider()
    {
        return array(
            array(
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
            ),
            array(
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
                "███████ ████   ██    \n",
                false,
                0
            ),
            array(
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
                "-----------------------\n",
                false,
                1,
                array(
                    'fullBlock' => '#',
                    'emptyBlock' => '-'
                )
            ),
            array(
                " ▄▄▄▄▄▄▄ ▄▄▄▄▄ ▄▄▄▄▄▄▄ \n" .
                " █ ▄▄▄ █  █▄▀  █ ▄▄▄ █ \n" .
                " █ ███ █  ▀█▀▄ █ ███ █ \n" .
                " █▄▄▄▄▄█ ▄ ▄▀█ █▄▄▄▄▄█ \n" .
                " ▄▄ ▄▄ ▄ ▀██▀█ ▄     ▄ \n" .
                "  ▄▄██▄▄▄▄▀█ ▀▄█ █▀ ▄▄ \n" .
                "  ▄▄  ▄▄▄  █▀  ▄  ▄▀ █ \n" .
                " ▄▄▄▄▄▄▄ ▀▄█▀█▄▄▄▄█ ▀  \n" .
                " █ ▄▄▄ █ ▄▄▀██▀▄▄ █▀▄▄ \n" .
                " █ ███ █ ▀▀▄▀▀ ▄▀▄█▀▄▄ \n" .
                " █▄▄▄▄▄█ ███▄ ▀▀█▄▀▀▀▀ \n",
                true
            ),
            array(
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
                "▀▀▀▀▀▀▀ ▀▀▀▀   ▀▀    \n",
                true,
                0
            ),
            array(
                "                         \n" .
                "  █▀▀▀▀▀█ ▀█▀█▀ █▀▀▀▀▀█  \n" .
                "  █ ███ █  ██▄  █ ███ █  \n" .
                "  █ ▀▀▀ █   ▀▄█ █ ▀▀▀ █  \n" .
                "  ▀▀▀▀▀▀▀ █▄█▄█ ▀▀▀▀▀▀▀  \n" .
                "  ▀▀ ██ ▀  ██ █ █ ▄▄  ▀  \n" .
                "   ▀▀▀▀▀▀▀▀ █▄ ▀▀ ▀ ▄▀█  \n" .
                "   ▀▀  ▀▀▀▄ █▄▄ ▀  █ ▄▀  \n" .
                "  █▀▀▀▀▀█  ▀█▄██▀▀▀█▄    \n" .
                "  █ ███ █ ██ ██ ▀█ █▄▀▀  \n" .
                "  █ ▀▀▀ █ ▄▄█  ▄█▄▀█▄██  \n" .
                "  ▀▀▀▀▀▀▀ ▀▀▀▀   ▀▀      \n",
                true,
                2
            ),
            array(
                "                           \n" .
                "   ▄▄▄▄▄▄▄ ▄▄▄▄▄ ▄▄▄▄▄▄▄   \n" .
                "   █ ▄▄▄ █  █▄▀  █ ▄▄▄ █   \n" .
                "   █ ███ █  ▀█▀▄ █ ███ █   \n" .
                "   █▄▄▄▄▄█ ▄ ▄▀█ █▄▄▄▄▄█   \n" .
                "   ▄▄ ▄▄ ▄ ▀██▀█ ▄     ▄   \n" .
                "    ▄▄██▄▄▄▄▀█ ▀▄█ █▀ ▄▄   \n" .
                "    ▄▄  ▄▄▄  █▀  ▄  ▄▀ █   \n" .
                "   ▄▄▄▄▄▄▄ ▀▄█▀█▄▄▄▄█ ▀    \n" .
                "   █ ▄▄▄ █ ▄▄▀██▀▄▄ █▀▄▄   \n" .
                "   █ ███ █ ▀▀▄▀▀ ▄▀▄█▀▄▄   \n" .
                "   █▄▄▄▄▄█ ███▄ ▀▀█▄▀▀▀▀   \n" .
                "                           \n",
                true,
                3
            ),
            array(
                "._______._____._______.\n" .
                ".#.___.#..#_¯..#.___.#.\n" .
                ".#.###.#..¯#¯_.#.###.#.\n" .
                ".#_____#._._¯#.#_____#.\n" .
                ".__.__._.¯##¯#._....._.\n" .
                "..__##____¯#.¯_#.#¯.__.\n" .
                "..__..___..#¯.._.._¯.#.\n" .
                "._______.¯_#¯#____#.¯..\n" .
                ".#.___.#.__¯##¯__.#¯__.\n" .
                ".#.###.#.¯¯_¯¯._¯_#¯__.\n" .
                ".#_____#.###_.¯¯#_¯¯¯¯.\n",
                true,
                1,
                array(
                    'fullBlock' => '#',
                    'emptyBlock' => '.',
                    'upplerHalfBlock' => '¯',
                    'lowerHalfBlock' => '_'
                )
            ),
        );
    }

    /**
     * @dataProvider additionProvider
     */
    public function testTextRender(
        $expected,
        $compact = false,
        $margin = 1,
        array $blocks = array(),
        $content = 'foobar'
    ) {
        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );

        $this->renderer->setCompact($compact);
        $this->assertEquals((boolean)$compact, $this->renderer->getCompact());

        $this->renderer->setMargin($margin);
        $this->assertEquals($margin, $this->renderer->getMargin());

        if (!empty($blocks)) {
            if (isset($blocks['fullBlock'])) {
                $this->renderer->setFullBlock($blocks['fullBlock']);
                $this->assertEquals($blocks['fullBlock'], $this->renderer->getFullBlock());
            }

            if (isset($blocks['emptyBlock'])) {
                $this->renderer->setEmptyBlock($blocks['emptyBlock']);
                $this->assertEquals($blocks['emptyBlock'], $this->renderer->getEmptyBlock());
            }

            if (isset($blocks['upplerHalfBlock'])) {
                $this->renderer->setUpperHalfBlock($blocks['upplerHalfBlock']);
                $this->assertEquals($blocks['upplerHalfBlock'], $this->renderer->getUpperHalfBlock());
            }

            if (isset($blocks['lowerHalfBlock'])) {
                $this->renderer->setLowerHalfBlock($blocks['lowerHalfBlock']);
                $this->assertEquals($blocks['lowerHalfBlock'], $this->renderer->getLowerHalfBlock());
            }
        }

        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }
}
