<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Service\ThumbnailService;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Flow\Mvc\ActionRequest;

class AssetImageSourceHelper extends AbstractImageSourceHelper implements ScalableImageSourceHelperInterface
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
     * @var Image
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
     * @param ImageInterface $asset
     */
    public function __construct(ImageInterface $asset)
    {
        $this->asset = $asset;
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

    public function scale(float $factor): ImageSourceHelperInterface
    {
        $scaledHelper = clone($this);

        if ($this->targetWidth) {
            $scaledHelper->setWidth(round($factor * $this->targetWidth));
        } else {
            $scaledHelper->setWidth(round($factor * $this->asset->getWidth()));
        }

        if ($this->targetHeight) {
            $scaledHelper->setHeight(round($factor * $this->targetHeight));
        } else if (!$this->targetWidth) {
            $scaledHelper->setHeight(round($factor * $this->asset->getHeight()));
        }

        return $scaledHelper;
    }

    public function src(): string
    {
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
            $async
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

    public function getCurrentWidth() : int
    {
        if ($this->targetWidth) {
            return $this->targetWidth;
        } elseif ($this->targetHeight) {
            return round($this->targetHeight * $this->asset->getWidth() / $this->asset->getHeight());
        } else {
            return $this->baseWidth;
        }
    }


    public function getCurrentHeight() : int
    {
        if ($this->targetHeight) {
            return $this->targetHeight;
        } elseif ($this->targetWidth) {
            return round($this->targetWidth *  $this->asset->getHeight() / $this->asset->getWidth());
        } else {
            return $this->baseHeight;
        }
    }
}
