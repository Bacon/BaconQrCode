<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\BitArray;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\Version;
use BaconQrCode\Renderer\Backend\Text;
use BaconQrCode\Renderer\Renderer;
use BaconQrCode\Writer;
use PHPUnit_Framework_TestCase as TestCase;

class TextRenderingTest extends TestCase
{
    /**
     * @var Text
     */
    protected $backend;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Writer
     */
    protected $writer;

    public function setUp()
    {
        $this->backend = new Text();
        $this->renderer = new Renderer($this->backend);
        $this->writer = new Writer($this->renderer);
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
        $this->assertEquals($expected, $this->renderer->render($qrCode, 1, 1, 0));
        $this->assertEquals($expected, $this->renderer->render($qrCode, 40, 40, 0));
        $this->assertEquals($expected, $this->renderer->render($qrCode, 100, 10,  0));
    }
}