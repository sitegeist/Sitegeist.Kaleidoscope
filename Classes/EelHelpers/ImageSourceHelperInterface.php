<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;

/**
 * @deprecated use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;
 */
interface ImageSourceHelperInterface
{
    /** deprecated methods with variing mutability */

    /**
     * @deprecated use withWidth
     */
    public function setWidth(int $width, bool $preserveAspect = false): ImageSourceInterface;

    /**
     * @deprecated use withHeight
     */
    public function setHeight(int $height, bool $preserveAspect = false): ImageSourceInterface;

    /**
     * @deprecated use withDimension
     */
    public function setDimensions(int $width, int $height): ImageSourceInterface;

    /**
     * @deprecated use withFormat
     */
    public function setFormat(string $format): ImageSourceInterface;

    /**
     * @deprecated use withTitle
     */
    public function setTitle(?string $title): void;

    /**
     * @deprecated use withAlt
     */
    public function setAlt(?string $alt): void;

    /**
     * @deprecated use withThumbnailPreset
     */
    public function applyPreset(string $name): ImageSourceInterface;

    /**
     * @deprecated use withThumbnailPreset
     */
    public function applyThumbnailPreset(string $name): ImageSourceInterface;

    /**
     * @deprecated use withVariantPreset
     */
    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface;
}
