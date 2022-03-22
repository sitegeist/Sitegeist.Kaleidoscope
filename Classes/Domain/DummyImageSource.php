<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

class DummyImageSource extends AbstractScalableImageSource
{
    /**
     * @var string
     */
    protected $backgroundColor;

    /**
     * @var string
     */
    protected $foregroundColor;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri, ?int $baseWidth = null, ?int $baseHeight = null, ?string $backgroundColor = null, ?string $foregroundColor = null, ?string $text = null)
    {
        $this->baseUri = $baseUri;
        $this->baseWidth = $baseWidth ?? 600;
        $this->baseHeight = $baseHeight ?? 400;
        $this->backgroundColor = $backgroundColor ?? '999';
        $this->foregroundColor = $foregroundColor ?? 'fff';
        $this->text = $text ?? '';
    }

    /**
     * @deprecated use constructor
     */
    public function setBaseWidth(int $baseWidth): void
    {
        $this->baseWidth = $baseWidth;
    }

    /**
     * @deprecated use constructor
     */
    public function setBaseHeight(int $baseHeight): void
    {
        $this->baseHeight = $baseHeight;
    }

    /**
     * @deprecated use constructor
     */
    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @deprecated use constructor
     */
    public function setForegroundColor(string $foregroundColor): void
    {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * @deprecated use constructor
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Use the variant generated from the given variant preset in this image source.
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     *
     * @return ImageSourceInterface
     */
    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface
    {
        /** @var DummyImageSource $newSource */
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
            'h' => $this->getCurrentHeight(),
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

        return $this->baseUri.'?'.http_build_query($arguments);
    }
}
