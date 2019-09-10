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
    public function getPreset()
    {
        return $this->fusionValue('preset');
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

        if ($preset = $this->getPreset()) {
            $helper = $helper->applyPreset($preset);
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
