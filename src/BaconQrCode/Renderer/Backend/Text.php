<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Backend;

use BaconQrCode\Exception;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Gray;

/**
 * Bitmap backend.
 */
class Text implements BackendInterface
{
    /**
     * Matrix used to generate code.
     *
     * @var array
     */
    protected $matrix = array();

    /**
     * The height of matrix.
     *
     * @var int
     */
    protected $matrixWidth = 0;

    /**
     * The width of matrix.
     *
     * @var int
     */
    protected $matrixHeight = 0;

    /**
     * Char used for full block
     * UTF-8 FULL BLOCK (U+2588)
     *
     * @var string
     * @link http://www.fileformat.info/info/unicode/char/2588/index.htm
     */
    protected $fullBlock = "\xE2\x96\x88";

    /**
     * Char used for empty space
     *
     * @var string
     */
    protected $emptyBlock = ' ';

    /**
     * Colors used for drawing.
     *
     * @var array
     */
    protected $colors = array();

    /**
     * The upper bound for recognising a gray color as black.
     * @var int
     */
    protected $monochromeThreshold = 10;


    /**
     * init(): defined by BackendInterface.
     *
     * @see    BackendInterface::init()
     * @param  integer $width
     * @param  integer $height
     * @param  integer $blockSize
     * @return void
     */
    public function init($width, $height, $blockSize)
    {
        $this->matrixWidth = $this->matrixHeight = 0;
        $this->matrix = array();
    }

    /**
     * addColor(): defined by BackendInterface.
     *
     * @see    BackendInterface::addColor()
     * @param  string         $id
     * @param  ColorInterface $color
     * @return void
     * @throws Exception\RuntimeException
     */
    public function addColor($id, ColorInterface $color)
    {
        if(!$color instanceof Gray){
            $color = $color->toGray();
        }

        $this->colors[$id] = $color->getGray() <= $this->monochromeThreshold;
    }

    /**
     * drawBackground(): defined by BackendInterface.
     *
     * @see    BackendInterface::drawBackground()
     * @param  string $colorId
     * @return void
     */
    public function drawBackground($colorId){}

    /**
     * drawBlock(): defined by BackendInterface.
     *
     * @see    BackendInterface::drawBlock()
     * @param  integer $x
     * @param  integer $y
     * @param  string  $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId)
    {
        $this->matrix[$x][$y] = $this->colors[$colorId];
        $this->matrixWidth  = max($this->matrixWidth,  $x);
        $this->matrixHeight = max($this->matrixHeight, $y);
    }

    /**
     * getByteStream(): defined by BackendInterface.
     *
     * @see    BackendInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
        $result = '';
        for ($y = 0; $y <= $this->matrixHeight; $y++) {
            for ($x = 0; $x <= $this->matrixWidth; $x++) {
                if (isset($this->matrix[$x][$y]) && $this->matrix[$x][$y] == 1) {
                    $result .= $this->fullBlock;
                } else {
                    $result .= $this->emptyBlock;
                }
            }
            $result .= "\n";
        }

        return $result;
    }

    /**
     * Set upper bound for gray color to be recognised as black (full block).
     *
     * @param int $monochromeThreshold
     */
    public function setMonochromeThreshold($monochromeThreshold)
    {
        $this->monochromeThreshold = $monochromeThreshold;
    }

    /**
     * Get upper bound for gray color to be recognised as black (full block).
     *
     * @return int
     */
    public function getMonochromeThreshold()
    {
        return $this->monochromeThreshold;
    }

    /**
     * Set char used as full block (occupied space, "black").
     *
     * @param string $fullBlock
     */
    public function setFullBlock($fullBlock)
    {
        $this->fullBlock = $fullBlock;
    }

    /**
     * Get char used as full block (occupied space, "black").
     *
     * @return string
     */
    public function getFullBlock()
    {
        return $this->fullBlock;
    }

    /**
     * Set char used as empty block (empty space, "white").
     *
     * @param string $emptyBlock
     */
    public function setEmptyBlock($emptyBlock)
    {
        $this->emptyBlock = $emptyBlock;
    }

    /**
     * Get char used as empty block (empty space, "white").
     *
     * @return string
     */
    public function getEmptyBlock()
    {
        return $this->emptyBlock;
    }


}