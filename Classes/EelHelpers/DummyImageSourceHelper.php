<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;

class DummyImageSourceHelper extends AbstractScalableImageSourceHelper
{
    protected $baseWidth = 600;

    protected $baseHeight = 400;

    protected $backgroundColor = '999';

    protected $foregroundColor = 'fff';

    protected $text = null;

    protected $baseUri = '';

    /**
     * @param ControllerContext $controllerContext
     */
    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
    }

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

    /**
     * @return string
     */
    public function src(): string
    {
        $arguments = [
            'w' => $this->getCurrentWidth(),
            'h' => $this->getCurrentHeight()
        ];

        if ($this->backgroundColor) {
            $arguments['bg'] = $this->backgroundColor;
        }

        if ($this->foregroundColor) {
            $arguments['fg'] = $this->foregroundColor;
        }

        if ($this->text) {
            $arguments['t'] = $this->text;
        }

        if ($this->targetFormat) {
            $arguments['f'] = $this->targetFormat;
        }

        $uri = $this->baseUri . '?' . http_build_query ($arguments);
        return $uri;
    }
}
