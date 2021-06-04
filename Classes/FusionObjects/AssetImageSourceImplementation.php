<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\ImageInterface;
use Sitegeist\Kaleidoscope\EelHelpers\AssetImageSourceHelper;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\UriImageSourceHelper;

class AssetImageSourceImplementation extends AbstractImageSourceImplementation
{
    /**
     * @Flow\Inject
     *
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string[]
     */
    protected $nonScalableMediaTypes = [
        'image/svg+xml',
    ];

    /**
     * @return ImageInterface|null
     */
    public function getAsset(): ?ImageInterface
    {
        return $this->fusionValue('asset');
    }

    /**
     * @return bool|null
     */
    public function getAsync(): ?bool
    {
        return $this->fusionValue('async');
    }

    /**
     * Create helper and initialize with the default values.
     *
     * @return ImageSourceHelperInterface|null
     */
    public function createHelper(): ?ImageSourceHelperInterface
    {
        $asset = $this->getAsset();
        if ($asset === null) {
            return null;
        }

        if (in_array($asset->getResource()->getMediaType(), $this->nonScalableMediaTypes, true)) {
            $uri = $this->resourceManager->getPublicPersistentResourceUri($asset->getResource());
            if (is_string($uri)) {
                $helper = new UriImageSourceHelper($uri);
                if ($title = $this->getTitle()) {
                    $helper->setTitle($title);
                }
                if ($alt = $this->getAlt()) {
                    $helper->setAlt($alt);
                }

                return $helper;
            } else {
                return null;
            }
        }

        $helper = new AssetImageSourceHelper($asset);
        $helper->setAsync((bool) $this->getAsync());
        $helper->setRequest($this->getRuntime()->getControllerContext()->getRequest());

        if ($title = $this->getTitle()) {
            $helper->setTitle($title);
        }

        if ($alt = $this->getAlt()) {
            $helper->setAlt($alt);
        }

        return $helper;
    }
}
