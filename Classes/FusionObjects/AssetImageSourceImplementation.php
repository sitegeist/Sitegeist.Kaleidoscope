<?php
namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\AssetImageSourceHelper;
use Sitegeist\Kaleidoscope\EelHelpers\UriImageSourceHelper;

class AssetImageSourceImplementation extends AbstractImageSourceImplementation
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var array
     */
    protected $nonScalableMediaTypes = [
        'image/svg+xml'
    ];

    /**
     * @return mixed
     */
    public function getAsset()
    {
        return $this->fusionValue('asset');
    }

    /**
     * @return mixed
     */
    public function getAsync()
    {
        return $this->fusionValue('async');
    }

    /**
     * Create helper and initialize with the default values
     *
     * @return ImageSourceHelperInterface|null
     */
    public function createHelper() : ?ImageSourceHelperInterface
    {
        $asset = $this->getAsset();
        if (!$asset) {
            return null;
        }

        if (in_array($asset->getResource()->getMediaType(), $this->nonScalableMediaTypes)) {
            $uri = $this->resourceManager->getPublicPersistentResourceUri($asset->getResource());
            return new UriImageSourceHelper($uri);
        }

        $helper = new AssetImageSourceHelper($asset);
        $helper->setAsync((bool)$this->getAsync());
        $helper->setRequest($this->getRuntime()->getControllerContext()->getRequest());

        return $helper;
    }
}
