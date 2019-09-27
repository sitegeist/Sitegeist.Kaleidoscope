<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\UriImageSourceHelper;

class UriImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->fusionValue('uri');
    }

    /**
     * Create helper and initialize with the default values
     *
     * @return ImageSourceHelperInterface
     */
    public function evaluate(): ImageSourceHelperInterface
    {
        return new UriImageSourceHelper($this->getUri());
    }
}
