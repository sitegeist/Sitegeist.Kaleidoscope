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

    public function withWidth(int $targetWidth, bool $preserveAspect = false): ImageSourceInterface;

    public function withHeight(int $targetHeight, bool $preserveAspect = false): ImageSourceInterface;

    public function withThumbnailPreset(string $format): ImageSourceInterface;

    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface;

    public function src(): string;

    public function srcset(string $mediaDescriptors): string;

    public function title(): ?string;

    public function alt(): ?string;

    public function width(): ?int;

    public function height(): ?int;

    public function __toString(): string;
}
