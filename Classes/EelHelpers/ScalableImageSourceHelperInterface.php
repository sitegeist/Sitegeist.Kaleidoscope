<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Sitegeist\Kaleidoscope\Domain\ScalableImageSourceInterface;

/**
 * @deprecated use Sitegeist\Kaleidoscope\Domain\ScalableImageSourceInterface;
 */
interface ScalableImageSourceHelperInterface extends ScalableImageSourceInterface
{
    /**
     * @deprecated use Sitegeist\Kaleidoscope\Domain\ScalableImageSourceInterface->width
     */
    public function getCurrentWidth(): ?int;

    /**
     * @deprecated use Sitegeist\Kaleidoscope\Domain\ScalableImageSourceInterface->width
     */
    public function getCurrentHeight(): ?int;
}
