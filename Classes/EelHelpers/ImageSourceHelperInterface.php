<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Eel\ProtectedContextAwareInterface;

interface ImageSourceHelperInterface extends ProtectedContextAwareInterface
{
    public function setWidth(int $width = null, bool $preserveAspect = false): ImageSourceHelperInterface;

    public function setHeight(int $height = null, bool $preserveAspect = false): ImageSourceHelperInterface;

    public function setDimensions(int $width = null, int $height = null): ImageSourceHelperInterface;

    public function setFormat(string $format = null) : ImageSourceHelperInterface;

    /**
     * @param string $name
     * @deprecated use applyThumbnailPreset
     * @return ImageSourceHelperInterface
     */
    public function applyPreset(string $name): ImageSourceHelperInterface;

    public function applyThumbnailPreset(string $name): ImageSourceHelperInterface;

    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceHelperInterface;

    public function src(): string;

    public function srcset($mediaDescriptors): string;

    public function __toString(): string;
}
