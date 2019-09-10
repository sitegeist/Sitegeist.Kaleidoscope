<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;

abstract class AbstractImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->fusionValue('width');
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->fusionValue('height');
    }

    /**
     * @return mixed
     */
    public function getThumbnailPreset()
    {
        return $this->fusionValue('thumbnailPreset') ?? $this->fusionValue('preset');
    }

    /**
     * @return mixed
     */
    public function getVariantPreset()
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

        return $helper;
    }

    /**
     * Create helper
     *
     * @return ImageSourceHelperInterface|null
     */
    abstract protected function createHelper(): ?ImageSourceHelperInterface;
}
