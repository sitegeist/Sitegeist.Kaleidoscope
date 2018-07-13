<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;

class DummyImageSourceHelper extends AbstractImageSourceHelper
{
    protected $baseWidth = 600;

    protected $baseHeight = 400;

    protected $backgroundColor = '999';

    protected $foregroundColor = 'fff';

    protected $text = null;

    /**
     * @param int $baseWidth
     */
    public function setBaseWidth(int $baseWidth): void
    {
        $this->baseWidth = $baseWidth;
    }

    /**
     * @param int $baseHeight
     */
    public function setBaseHeight(int $baseHeight): void
    {
        $this->baseHeight = $baseHeight;
    }

    /**
     * @param string $backgroundColor
     */
    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @param string $foregroundColor
     */
    public function setForegroundColor(string $foregroundColor): void
    {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * @param null $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    public function scale(float $factor): ImageSourceHelperInterface
    {
        $scaledHelper = clone($this);
        $scaledHelper->setBaseWidth(round($factor * $this->baseWidth));
        $scaledHelper->setBaseHeight(round($factor * $this->baseHeight));

        if ($this->targetWidth) {
            $scaledHelper->setWidth(round($factor * $this->targetWidth));
        }
        if ($this->targetHeight) {
            $scaledHelper->setHeight(round($factor * $this->targetHeight));
        }

        return $scaledHelper;
    }

    public function src(): string
    {
        $url = 'https://dummyimage.com';

        $url .= '/' . $this->getWidth() . 'x' . $this->getHeight();
        $url .= '/' . ($this->backgroundColor ?: '000');
        $url .= '/' . ($this->foregroundColor ?: 'fff');

        if ($this->text) {
            $url .=  '&text=' . urlencode($this->text);
        }

        return $url;
    }

    public function getWidth() : int
    {
        if ($this->targetWidth) {
            return $this->targetWidth;
        } elseif ($this->targetHeight) {
            return round($this->targetHeight * $this->baseWidth / $this->baseHeight);
        } else {
            return $this->baseWidth;
        }
    }


    public function getHeight() : int
    {
        if ($this->targetHeight) {
            return $this->targetHeight;
        } elseif ($this->targetWidth) {
            return round($this->targetWidth * $this->baseHeight / $this->baseWidth);
        } else {
            return $this->baseHeight;
        }
    }
}
