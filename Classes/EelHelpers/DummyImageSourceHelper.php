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
     * Use the variant generated from the given variant preset in this image source
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     * @return ImageSourceHelperInterface
     */
    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceHelperInterface
    {
        /** @var DummyImageSourceHelper $newSource */
        $newSource = parent::useVariantPreset($presetIdentifier, $presetVariantName);

        if ($newSource->targetImageVariant !== []) {
            $targetBox = $this->estimateDimensionsFromVariantPresetAdjustments($presetIdentifier, $presetVariantName);
            $newSource->baseWidth = $targetBox->getWidth();
            $newSource->baseHeight = $targetBox->getHeight();
        }

        return $newSource;
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

        return $this->baseUri . '?' . http_build_query($arguments);
    }

}
