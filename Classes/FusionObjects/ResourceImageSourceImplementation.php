<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\ResourceImageSourceHelper;

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
    public function evaluate(): ImageSourceHelperInterface
    {
        return new ResourceImageSourceHelper($this->getPackage(), $this->getPath());
    }
}
