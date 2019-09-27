<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

interface ScalableImageSourceHelperInterface extends ImageSourceHelperInterface
{
    public function scale(float $factor): ImageSourceHelperInterface;

    public function getCurrentWidth(): int;

    public function getCurrentHeight(): int;
}
