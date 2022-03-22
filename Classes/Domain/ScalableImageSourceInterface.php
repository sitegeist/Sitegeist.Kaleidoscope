<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

interface ScalableImageSourceInterface extends ImageSourceInterface
{
    public function scale(float $factor): ImageSourceInterface;

    /**
     * @deprecated use width
     */
    public function getCurrentWidth(): ?int;

    /**
     * @deprecated use height
     */
    public function getCurrentHeight(): ?int;
}
