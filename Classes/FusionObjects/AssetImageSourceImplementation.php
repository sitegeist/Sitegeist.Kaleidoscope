<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\ImageInterface;
use Sitegeist\Kaleidoscope\Domain\AssetImageSource;
use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;
use Sitegeist\Kaleidoscope\Domain\UriImageSource;

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
     * @return ImageSourceInterface|null
     */
    public function createHelper(): ?ImageSourceInterface
    {
        $asset = $this->getAsset();
        if ($asset === null) {
            return null;
        }

        if (in_array($asset->getResource()->getMediaType(), $this->nonScalableMediaTypes, true)) {
            $uri = $this->resourceManager->getPublicPersistentResourceUri($asset->getResource());
            if (is_string($uri)) {
                $helper = new UriImageSource($uri);
                if ($title = $this->getTitle()) {
                    $helper = $helper->withTitle($title);
                }
                if ($alt = $this->getAlt()) {
                    $helper = $helper->withAlt($alt);
                }

                return $helper;
            } else {
                return null;
            }
        }

        $helper = new AssetImageSource(
            $asset,
            (bool) $this->getAsync(),
            $this->getRuntime()->getControllerContext()->getRequest()
        );

        return $helper;
    }
}
