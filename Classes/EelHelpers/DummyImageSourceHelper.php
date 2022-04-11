<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Sitegeist\Kaleidoscope\Domain\DummyImageSource;

/**
 * @deprecated use Sitegeist\Kaleidoscope\Domain\DummyImageSource;
 */
class DummyImageSourceHelper extends DummyImageSource
{

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        parent::__construct(
            $this->baseUri = $baseUri,
            null,
            null,
            600,
            400
        );
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
     * @param string $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }
}
