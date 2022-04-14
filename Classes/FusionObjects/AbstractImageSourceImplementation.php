<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;

abstract class AbstractImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->fusionValue('width');
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->fusionValue('height');
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->fusionValue('format');
    }

    /**
     * @return string|null
     */
    public function getThumbnailPreset(): ?string
    {
        return $this->fusionValue('thumbnailPreset') ?? $this->fusionValue('preset');
    }

    /**
     * @return string|null
     */
    public function getVariantPreset(): ?string
    {
        return $this->fusionValue('variantPreset');
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->fusionValue('title');
    }

    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return $this->fusionValue('alt');
    }

    /**
     * Create helper and initialize width and height.
     *
     * @return ImageSourceInterface|null
     */
    public function evaluate(): ?ImageSourceInterface
    {
        $helper = $this->createHelper();
        if ($helper === null) {
            return $helper;
        }

        if ($thumbnailPreset = $this->getThumbnailPreset()) {
            $helper = $helper->withThumbnailPreset($thumbnailPreset);
        }

        if (($variantPreset = $this->getVariantPreset()) && (strpos($variantPreset, '::') !== false)) {
            [$presetIdentifier, $presetVariantName] = explode('::', $variantPreset, 2);
            $helper = $helper->withVariantPreset($presetIdentifier, $presetVariantName);
        }

        if ($width = $this->getWidth()) {
            $helper = $helper->withWidth($width);
        }

        if ($height = $this->getHeight()) {
            $helper = $helper->withHeight($height);
        }

        if ($format = $this->getFormat()) {
            $helper = $helper->withFormat($format);
        }

        if ($title = $this->getTitle()) {
            $helper = $helper->withTitle($title);
        }

        if ($alt = $this->getAlt()) {
            $helper = $helper->withAlt($alt);
        }

        return $helper;
    }

    /**
     * Create helper.
     *
     * @return ImageSourceInterface|null
     */
    abstract protected function createHelper(): ?ImageSourceInterface;
}
