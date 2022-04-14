<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Mvc\ActionRequest;
use Neos\Media\Domain\Model\ImageInterface;
use Sitegeist\Kaleidoscope\Domain\AssetImageSource;

/**
 * @deprecated use Sitegeist\Kaleidoscope\Domain\AssetImageSource;
 */
class AssetImageSourceHelper extends AssetImageSource
{
    /**
     * AssetImageSourceHelper constructor.
     *
     * @param ImageInterface $asset
     */
    public function __construct(ImageInterface $asset)
    {
        parent::__construct($asset);
    }

    /**
     * @param bool $async
     *
     * @deprecated use AssetImageSource->__construct
     */
    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    /**
     * @param ActionRequest $request
     *
     * @deprecated use AssetImageSource->__construct
     */
    public function setRequest(ActionRequest $request): void
    {
        $this->request = $request;
    }
}
