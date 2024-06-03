<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

interface ScalableImageSourceInterface extends ImageSourceInterface
{
    public function supportsUpscaling(): bool;
    public function scale(float $factor): ImageSourceInterface;
}
