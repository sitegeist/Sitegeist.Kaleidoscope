<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

interface ImageSourceInterface
{
    /** immutable methods */
    public function withAlt(?string $alt): ImageSourceInterface;

    public function withTitle(?string $title): ImageSourceInterface;

    public function withDimensions(int $targetWidth, int $targetHeight): ImageSourceInterface;

    public function withFormat(string $format): ImageSourceInterface;

    public function withWidth(int $targetWidth, bool $preserveAspect): ImageSourceInterface;

    public function withHeight(int $targetHeight, bool $preserveAspect): ImageSourceInterface;

    public function withThumbnailPreset(string $format): ImageSourceInterface;

    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface;

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

    public function src(): string;

    public function srcset(string $mediaDescriptors): string;

    public function title(): ?string;

    public function alt(): ?string;

    public function width(): ?int;

    public function height(): ?int;

    public function __toString(): string;
}
