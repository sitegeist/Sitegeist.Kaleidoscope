<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ImageVariant;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Model\VariantSupportInterface;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;

class AssetImageSourceHelper extends AbstractScalableImageSourceHelper
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
     * AssetImageSourceHelper constructor.
     *
     * @param ImageInterface $asset
     */
    public function __construct(ImageInterface $asset)
    {
        $this->asset = $asset;
        $this->baseWidth = $this->asset->getWidth();
        $this->baseHeight = $this->asset->getHeight();
    }

    /**
     * @param bool $async
     */
    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    /**
     * @param ActionRequest $request
     */
    public function setRequest(ActionRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * Use the variant generated from the given variant preset in this image source.
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     *
     * @return ImageSourceHelperInterface
     */
    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceHelperInterface
    {
        /**
         * @var AssetImageSourceHelper $newSource
         */
        $newSource = parent::useVariantPreset($presetIdentifier, $presetVariantName);

        if ($newSource->targetImageVariant !== []) {
            $asset = ($newSource->asset instanceof ImageVariant) ? $newSource->asset->getOriginalAsset() : $newSource->asset;
            if ($asset instanceof VariantSupportInterface) {
                $assetVariant = $asset->getVariant($newSource->targetImageVariant['presetIdentifier'], $newSource->targetImageVariant['presetVariantName']);
            } else {
                $assetVariant = null;
            }
            if ($assetVariant instanceof ImageVariant) {
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

        $width = $this->getCurrentWidth();
        $height = $this->getCurrentHeight();

        $async = $this->request ? $this->async : false;
        $allowCropping = true;
        $allowUpScaling = false;
        $thumbnailConfiguration = new ThumbnailConfiguration(
            $width,
            $width,
            $height,
            $height,
            $allowCropping,
            $allowUpScaling,
            $async,
            null,
            $this->targetFormat
        );

        $thumbnailData = $this->assetService->getThumbnailUriAndSizeForAsset(
            $this->asset,
            $thumbnailConfiguration,
            $this->request
        );

        if ($thumbnailData === null) {
            return '';
        }

        return $thumbnailData['src'];
    }
}
