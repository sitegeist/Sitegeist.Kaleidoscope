<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

class DummyImageSourceHelper extends AbstractScalableImageSourceHelper
{
    /**
     * @var string
     */
    protected $backgroundColor = '999';

    /**
     * @var string
     */
    protected $foregroundColor = 'fff';

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $baseUri = '';

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        $this->baseWidth = 600;
        $this->baseHeight = 400;
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
        return $this->baseUri . '?' . http_build_query(
                [
                    'w' => $this->getCurrentWidth(),
                    'h' => $this->getCurrentHeight(),
                    'bg' => $this->backgroundColor ?: '000',
                    'fg' => $this->foregroundColor ?: 'fff',
                    't' => trim($this->text ?: $this->getCurrentWidth() . ' x ' . $this->getCurrentHeight()),
                    'pi' => $this->targetImageVariant['presetIdentifier'] ?? '',
                    'pv' => $this->targetImageVariant['presetVariantName'] ?? ''
                ]
            );
    }
}
