<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;

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
     * Create helper and initialize width and height
     *
     * @return ImageSourceHelperInterface|null
     */
    public function evaluate(): ?ImageSourceHelperInterface
    {
        $helper = $this->createHelper();
        if ($helper === null) {
            return $helper;
        }

        if ($thumbnailPreset = $this->getThumbnailPreset()) {
            $helper = $helper->applyThumbnailPreset($thumbnailPreset);
        }

        if (($variantPreset = $this->getVariantPreset()) && (strpos($variantPreset, '::') !== false)) {
            [$presetIdentifier, $presetVariantName] = explode('::', $variantPreset, 2);
            $helper = $helper->useVariantPreset($presetIdentifier, $presetVariantName);
        }

        if ($width = $this->getWidth()) {
            $helper = $helper->setWidth($width);
        }

        if ($height = $this->getHeight()) {
            $helper = $helper->setHeight($height);
        }

        if ($format = $this->getFormat()) {
            $helper = $helper->setFormat($format);
        }

        return $helper;
    }

    /**
     * Create helper
     *
     * @return ImageSourceHelperInterface|null
     */
    abstract protected function createHelper(): ?ImageSourceHelperInterface;
}
