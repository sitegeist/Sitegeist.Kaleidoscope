<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;

class AssetImageSourceHelper extends AbstractScalableImageSourceHelper
{

    /**
     * @Flow\Inject
     * @var ThumbnailService
     */
    protected $thumbnailService;

    /**
     * @Flow\Inject
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
     * @var ActionRequest
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
     * @return string
     * @throws \Neos\Flow\Mvc\Routing\Exception\MissingActionNameException
     * @throws \Neos\Media\Exception\AssetServiceException
     * @throws \Neos\Media\Exception\ThumbnailServiceException
     */
    public function src(): string
    {
        if (!$this->asset instanceof AssetInterface) {
            return '';
        }

        $async = $this->request ? $this->async : false;
        $allowCropping = ($this->targetWidth && $this->targetHeight);
        $allowUpScaling = false;
        $thumbnailConfiguration = new ThumbnailConfiguration(
            $this->targetWidth,
            $this->targetWidth,
            $this->targetHeight,
            $this->targetHeight,
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
