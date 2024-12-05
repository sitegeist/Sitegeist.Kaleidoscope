<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\ServerRequestAttributes;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionRequestFactory;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetVariantInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Model\VariantSupportInterface;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class AssetImageSource extends AbstractScalableImageSource
{
    /**
     * @Flow\Inject
     *
     * @var ThumbnailService
     */
    protected $thumbnailService;

    /**
     * @Flow\Inject
     *
     * @var AssetService
     */
    protected $assetService;

    /**
     * @var UriFactoryInterface
     * @Flow\Inject
     */
    protected $uriFactory;

    /**
     * @var ServerRequestFactoryInterface
     * @Flow\Inject
     */
    protected $serverRequestFactory;

    /**
     * @var ActionRequestFactory
     * @Flow\Inject
     */
    protected $actionRequestFactory;

    /**
     * @var ImageInterface
     */
    protected $asset;

    /**
     * @var bool
     */
    protected $async = false;

    /**
     * @var ActionRequest|null
     */
    protected $request;

    /**
     * Runtime cache for the src uri for the asset.
     *
     * @var string|null
     */
    private $srcCache = null;

    /**
     * @param ImageInterface     $asset
     * @param string|null        $title
     * @param string|null        $alt
     * @param bool               $async
     * @param ActionRequest|null $request
     */
    public function __construct(ImageInterface $asset, ?string $title = null, ?string $alt = null, bool $async = true, ?ActionRequest $request = null)
    {
        parent::__construct($title, $alt);
        $this->asset = $asset;
        $this->request = $request;
        $this->async = $async;
        $this->baseWidth = $this->asset->getWidth();
        $this->baseHeight = $this->asset->getHeight();
    }

    public function supportsUpscaling(): bool
    {
        return false;
    }

    /**
     * Use the variant generated from the given variant preset in this image source.
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     *
     * @return ImageSourceInterface
     */
    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface
    {
        /**
         * @var AssetImageSource $newSource
         */
        $newSource = parent::withVariantPreset($presetIdentifier, $presetVariantName);

        if ($newSource->targetImageVariant !== []) {
            $asset = ($newSource->asset instanceof AssetVariantInterface && $newSource->asset instanceof ImageInterface) ? $newSource->asset->getOriginalAsset() : $newSource->asset;
            if ($asset instanceof VariantSupportInterface) {
                $assetVariant = $asset->getVariant($newSource->targetImageVariant['presetIdentifier'], $newSource->targetImageVariant['presetVariantName']);
            } else {
                $assetVariant = null;
            }
            if ($assetVariant instanceof AssetVariantInterface && $assetVariant instanceof ImageInterface) {
                $newSource->asset = $assetVariant;
                $newSource->baseWidth = $assetVariant->getWidth();
                $newSource->baseHeight = $assetVariant->getHeight();
            } else {
                // if no alternate variant is found we estimate the target dimensions
                $targetDimensions = $this->estimateDimensionsFromVariantPresetAdjustments($presetIdentifier, $presetVariantName);
                $newSource->baseWidth = $targetDimensions->getWidth();
                $newSource->baseHeight = $targetDimensions->getHeight();
            }
        }

        return $newSource;
    }

    /**
     * @throws \Neos\Flow\Mvc\Routing\Exception\MissingActionNameException
     * @throws \Neos\Media\Exception\AssetServiceException
     * @throws \Neos\Media\Exception\ThumbnailServiceException
     *
     * @return string
     */
    public function src(): string
    {
        if (!$this->asset instanceof AssetInterface) {
            return '';
        }

        if ($this->srcCache !== null) {
            return $this->srcCache;
        }

        $width = $this->getCurrentWidth();
        $height = $this->getCurrentHeight();

        $allowCropping = true;
        $allowUpScaling = $this->supportsUpscaling();
        $thumbnailConfiguration = new ThumbnailConfiguration(
            $width,
            $width,
            $height,
            $height,
            $allowCropping,
            $allowUpScaling,
            $this->async,
            $this->targetQuality,
            $this->targetFormat
        );

        if ($this->request instanceof ActionRequest) {
            $request = $this->request;
        } else {
            $uri = $this->uriFactory->createUri('http://localhost');
            $httpRequest = $this->serverRequestFactory->createServerRequest('GET', $uri)
                                                      ->withAttribute(
                                                          ServerRequestAttributes::ROUTING_PARAMETERS,
                                                          RouteParameters::createEmpty()->withParameter('requestUriHost', $uri->getHost())
                                                      );
            $request = $this->actionRequestFactory->createActionRequest($httpRequest);
        }

        $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset(
            $this->asset,
            $thumbnailConfiguration,
            $request
        );

        $this->srcCache = ($thumbnailData === null) ? '' : $thumbnailData['src'];

        return $this->srcCache;
    }

    public function __clone(): void
    {
        $this->srcCache = null;
    }

    public function dataSrc(): string
    {
        if (!$this->asset instanceof AssetInterface) {
            return '';
        }

        $width = $this->getCurrentWidth();
        $height = $this->getCurrentHeight();

        $allowCropping = true;
        $allowUpScaling = $this->supportsUpscaling();
        $thumbnailConfiguration = new ThumbnailConfiguration(
            $width,
            $width,
            $height,
            $height,
            $allowCropping,
            $allowUpScaling,
            false,
            $this->targetQuality,
            $this->targetFormat
        );

        $thumbnailImage = $this->thumbnailService->getThumbnail($this->asset, $thumbnailConfiguration);

        if ($thumbnailImage instanceof ImageInterface) {
            if ($stream = $thumbnailImage->getResource()->getStream()) {
                if (is_resource($stream)) {
                    if ($content = stream_get_contents($stream)) {
                        $mediaType = $thumbnailImage->getResource()->getMediaType();
                        if (is_string($content)) {
                            return 'data:' . $mediaType . ';base64,' . base64_encode($content);
                        }
                    }
                }
            }
        }

        return '';
    }
}
