<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Text;

use BaconQrCode\Exception;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\RendererInterface;

/**
 * Plaintext renderer.
 */
class Plain implements RendererInterface
{
    /**
     * Margin around the QR code, also known as quiet zone.
     *
     * @var integer
     */
    protected $margin = 1;

    /**
     * Whether QR code render compact.
     *
     * @var boolean
     */
    protected $compact = false;

    /**
     * Char used for full block.
     *
     * UTF-8 FULL BLOCK (U+2588)
     *
     * @var  string
     * @link http://www.fileformat.info/info/unicode/char/2588/index.htm
     */
    protected $fullBlock = "\xE2\x96\x88";

    /**
     * Char used for upper half block.
     *
     * UTF-8 UPPER HALF BLOCK (U+2580)
     * @var string
     * @link http://www.fileformat.info/info/unicode/char/2580/index.htm
     */
    protected $upperHalfBlock = "\xE2\x96\x80";

    /**
     * Char used for lower half block.
     *
     * UTF-8 LOWER HALF BLOCK (U+2584)
     * @var string
     * @link http://www.fileformat.info/info/unicode/char/2584/index.htm
     */
    protected $lowerHalfBlock = "\xE2\x96\x84";

    /**
     * Char used for empty space
     *
     * @var string
     */
    protected $emptyBlock = ' ';

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
     * Set char used as upper half block (upper half occupied space, lower half empty space).
     *
     * @param string $upperHalfBlock
     */
    public function setUpperHalfBlock($upperHalfBlock)
    {
        $this->upperHalfBlock = $upperHalfBlock;
    }

    /**
     * Get char used as upper half block (upper half occupied space, lower half empty space).
     *
     * @return string
     */
    public function getUpperHalfBlock()
    {
        return $this->upperHalfBlock;
    }

    /**
     * Set char used as lower half block (upper half empty space, lower half occupied space).
     *
     * @param string $lowerHalfBlock
     */
    public function setLowerHalfBlock($lowerHalfBlock)
    {
        $this->lowerHalfBlock = $lowerHalfBlock;
    }

    /**
     * Get char used as lower half block (upper half empty space, lower half occupied space).
     *
     * @return string
     */
    public function getLowerHalfBlock()
    {
        return $this->lowerHalfBlock;
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

    /**
     * Sets the margin around the QR code.
     *
     * @param  integer $margin
     * @return AbstractRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setMargin($margin)
    {
        if ($margin < 0) {
            throw new Exception\InvalidArgumentException('Margin must be equal to greater than 0');
        }

        $this->margin = (int) $margin;

        return $this;
    }

    /**
     * Gets the margin around the QR code.
     *
     * @return integer
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * Sets whether QR code render compact.
     *
     * @param boolean $compact
     */
    public function setCompact($compact)
    {
        $this->compact = (bool)$compact;
    }

    /**
     * Gets whether QR code render compact.
     *
     * @return boolean
     */
    public function getCompact()
    {
        return $this->compact;
    }

    /**
     * render(): defined by RendererInterface.
     *
     * @see    RendererInterface::render()
     * @param  QrCode $qrCode
     * @return string
     */
    public function render(QrCode $qrCode)
    {
        $result = '';
        $matrix = $qrCode->getMatrix();
        $width  = $matrix->getWidth();
        $margin = str_repeat($this->emptyBlock, $this->margin);

        // Top margin
        for ($x = 0; $x < $this->margin; $x++) {
            $result .= str_repeat($this->emptyBlock, $width + 2 * $this->margin)."\n";
        }

        // Body
        $array = $matrix->getArray();

        if ($this->compact) {
            $len = $array->getSize();
            $size = $array[0]->getSize();

            // If not an even rows, fill an empty blocks row
            if ($len % 2 !== 0) {
                $array->setSize($len + 1);
                for ($i = 0; $i < $size; $i++) {
                    $array[$len][$i] = 0;
                }
                $len++;
            }

            for ($i = 0; $i < $len; $i++) {
                // Each loop get 2 rows
                $even = $array[$i];
                $odd = $array[++$i];

                $result .= $margin; // left margin
                for ($j = 0; $j < $size; $j++) {
                    $upper = $even[$j];
                    $lower = $odd[$j];

                    if ($upper) {
                        $result .= $lower ? $this->fullBlock : $this->upperHalfBlock;
                    } else {
                        $result .= $lower ? $this->lowerHalfBlock : $this->emptyBlock;
                    }
                }
                $result .= $margin; // right margin
                $result .= "\n";
            }
        } else {
            foreach ($array as $row) {
                $result .= $margin; // left margin
                foreach ($row as $byte) {
                    $result .= $byte ? $this->fullBlock : $this->emptyBlock;
                }
                $result .= $margin; // right margin
                $result .= "\n";
            }
        }

        // Bottom margin
        for ($x = 0; $x < $this->margin; $x++) {
            $result .= str_repeat($this->emptyBlock, $width + 2 * $this->margin)."\n";
        }

        return $result;
    }
}
