<?php
namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
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
    public function evaluate() : ?ImageSourceHelperInterface
    {
        $helper = $this->createHelper();
        if (is_null($helper)) {
            return $helper;
        }

        if ($preset = $this->getPreset()) {
            $helper->applyPreset($preset);
        }

        if ($width = $this->getWidth()) {
            $helper->setWidth($width);
        }

        if ($height = $this->getHeight()) {
            $helper->setHeight($height);
        }

        return $helper;
    }

    /**
     * Create helper
     *
     * @return ImageSourceHelperInterface|null
     */
    abstract protected function createHelper() : ?ImageSourceHelperInterface;
}
