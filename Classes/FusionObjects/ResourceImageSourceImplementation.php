<?php
namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\ResourceImageSourceHelper;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ResourceImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->fusionValue('package');
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->fusionValue('path');
    }

    /**
     * Create helper and initialize with the default values
     *
     * @return ImageSourceHelperInterface
     */
    public function evaluate() : ImageSourceHelperInterface
    {
        return new ResourceImageSourceHelper($this->getPackage(), $this->getPath());
    }
}
