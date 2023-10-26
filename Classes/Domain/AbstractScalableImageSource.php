<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Media\Domain\Model\Adjustment\CropImageAdjustment;
use Neos\Media\Domain\Model\Adjustment\ImageAdjustmentInterface;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\ValueObject\Configuration\Adjustment;
use Neos\Media\Domain\ValueObject\Configuration\VariantPreset;
use Neos\Utility\ObjectAccess;
use Neos\Utility\Arrays;
use Sitegeist\Kaleidoscope\EelHelpers\ScalableImageSourceHelperInterface;

abstract class AbstractScalableImageSource extends AbstractImageSource implements ScalableImageSourceInterface, ScalableImageSourceHelperInterface
{
    /**
     * @var ImagineInterface
     *
     * @Flow\Inject
     */
    protected $imagineService;

    /**
     * @var int
     */
    protected $baseWidth;

    /**
     * @var int
     */
    protected $baseHeight;

    /**
     * @var bool
     */
    protected $allowUpScaling = false;

    /**
     * @param int|null $targetWidth
     * @param bool     $preserveAspect
     *
     * @return ScalableImageSourceInterface
     */
    public function withWidth(int $targetWidth = null, bool $preserveAspect = false): ScalableImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
        if ($preserveAspect === true) {
            if ($this->targetWidth && $this->targetHeight) {
                $aspect = $this->targetWidth / $this->targetHeight;
            } else {
                $aspect = $this->baseWidth / $this->baseHeight;
            }
            $newSource->targetHeight = (int) round($targetWidth / $aspect);
        }

        return $newSource;
    }

    /**
     * @param int|null $targetHeight
     * @param bool     $preserveAspect
     *
     * @return ScalableImageSourceInterface
     */
    public function withHeight(int $targetHeight = null, bool $preserveAspect = false): ScalableImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetHeight = $targetHeight;
        if ($preserveAspect === true) {
            if ($this->targetWidth && $this->targetHeight) {
                $aspect = $this->targetWidth / $this->targetHeight;
            } else {
                $aspect = $this->baseWidth / $this->baseHeight;
            }
            $newSource->targetWidth = (int) round($targetHeight * $aspect);
        }

        return $newSource;
    }

    /**
     * @param int $targetWidth
     * @param int $targetHeight
     *
     * @return ScalableImageSourceInterface
     */
    public function withDimensions(int $targetWidth, int $targetHeight): ScalableImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
        $newSource->targetHeight = $targetHeight;

        return $newSource;
    }


    /**
     * @param float $factor
     * @param bool $allowUpScaling
     *
     * @return ScalableImageSourceInterface
     */
    public function scale(float $factor, bool $allowUpScaling = false): ScalableImageSourceInterface
    {
        $scaledHelper = clone $this;
        $scaledHelper->allowUpScaling = $allowUpScaling;

        if ($this->targetWidth && $this->targetHeight) {
            $scaledHelper = $scaledHelper->withDimensions((int) round($factor * $this->targetWidth), (int) round($factor * $this->targetHeight));
        } elseif ($this->targetWidth) {
            $scaledHelper = $scaledHelper->withWidth((int) round($factor * $this->targetWidth));
        } elseif ($this->targetHeight) {
            $scaledHelper = $scaledHelper->withHeight((int) round($factor * $this->targetHeight));
        } else {
            $scaledHelper = $scaledHelper->withWidth((int) round($factor * $this->baseWidth));
        }

        return $scaledHelper;
    }

    /**
     * @deprecated use width()
     */
    public function getCurrentWidth(): ?int
    {
        return $this->width();
    }

    public function width(): ?int
    {
        if ($this->targetWidth) {
            return $this->targetWidth;
        }

        if ($this->targetHeight) {
            return (int) round($this->targetHeight * $this->baseWidth / $this->baseHeight);
        }

        return $this->baseWidth;
    }

    /**
     * @deprecated use height()
     */
    public function getCurrentHeight(): ?int
    {
        return $this->height();
    }

    public function height(): ?int
    {
        if ($this->targetHeight) {
            return $this->targetHeight;
        }

        if ($this->targetWidth) {
            return (int) round($this->targetWidth * $this->baseHeight / $this->baseWidth);
        }

        return $this->baseHeight;
    }

    /**
     * @param string $presetIdentifier
     * @param string $presetVariantName
     *
     * @return Box
     */
    protected function estimateDimensionsFromVariantPresetAdjustments(string $presetIdentifier, string $presetVariantName): Box
    {
        $imageBox = new Box(
            $this->baseWidth,
            $this->baseHeight
        );

        $assetVariantPreset = VariantPreset::fromConfiguration($this->variantPresets[$presetIdentifier]);
        foreach ($assetVariantPreset->variants()[$presetVariantName]->adjustments() as $adjustmentConfiguration) {
            $adjustment = $this->createAdjustment($adjustmentConfiguration);

            switch (true) {
                case $adjustment instanceof ResizeImageAdjustment:
                    $image = $this->imagineService->create($imageBox);
                    if ($adjustment->canBeApplied($image)) {
                        $image = $adjustment->applyToImage($image);

                        /** @phpstan-ignore-next-line */
                        return new Box((int) round($image->getSize()->getWidth()), (int) round($image->getSize()->getHeight()));
                    }
                    break;
                case $adjustment instanceof CropImageAdjustment:
                    $desiredAspectRatio = $adjustment->getAspectRatio();
                    if ($desiredAspectRatio !== null) {
                        [, , $newWidth, $newHeight] = CropImageAdjustment::calculateDimensionsByAspectRatio($this->baseWidth, $this->baseHeight, $desiredAspectRatio);
                    } else {
                        $newWidth = $adjustment->getWidth();
                        $newHeight = $adjustment->getHeight();
                    }

                    return new Box(
                        (int) round($newWidth),
                        (int) round($newHeight)
                    );
            }
        }

        return $imageBox;
    }

    /**
     * @param Adjustment $adjustmentConfiguration
     *
     * @return ImageAdjustmentInterface
     */
    protected function createAdjustment(Adjustment $adjustmentConfiguration): ImageAdjustmentInterface
    {
        $adjustmentClassName = $adjustmentConfiguration->type();
        if (!class_exists($adjustmentClassName)) {
            throw new \RuntimeException(sprintf('Unknown image variant adjustment type "%s".', $adjustmentClassName), 1568213194);
        }
        $adjustment = new $adjustmentClassName();
        if (!$adjustment instanceof ImageAdjustmentInterface) {
            throw new \RuntimeException(sprintf('Image variant adjustment "%s" does not implement "%s".', $adjustmentClassName, ImageAdjustmentInterface::class), 1568213198);
        }
        foreach ($adjustmentConfiguration->options() as $key => $value) {
            ObjectAccess::setProperty($adjustment, $key, $value);
        }

        if (!$adjustment instanceof ImageAdjustmentInterface) {
            throw new \RuntimeException(sprintf('Could not apply the %s adjustment to image because it does not implement the ImageAdjustmentInterface.', get_class($adjustment)), 1381400362);
        }

        return $adjustment;
    }

    /**
     * Render srcset Attribute for various media descriptors.
     *
     * If upscaling is not allowed and the width is greater than the base width,
     * use the base width.
     *
     * @param $mediaDescriptors
     * @param bool $allowUpScaling
     *
     * @return string
     */
    public function srcset($mediaDescriptors, bool $allowUpScaling = false): string
    {
        $srcsetArray = [];

        if (is_array($mediaDescriptors) || $mediaDescriptors instanceof \Traversable) {
            $descriptors = $mediaDescriptors;
        } else {
            $descriptors = Arrays::trimExplode(',', (string)$mediaDescriptors);
        }

        $srcsetType = null;
        $maxScaleFactor = min($this->baseWidth / $this->width(), $this->baseHeight / $this->height());

        foreach ($descriptors as $descriptor) {
            $hasDescriptor = preg_match('/^(?<width>[0-9]+)w$|^(?<factor>[0-9\\.]+)x$/u', $descriptor, $matches);

            if (!$hasDescriptor) {
                $this->logger->warning(sprintf('Invalid media descriptor "%s". Missing type "x" or "w"', $descriptor), LogEnvironment::fromMethodName(__METHOD__));
                continue;
            }

            if (!$srcsetType) {
                $srcsetType = isset($matches['width']) ? 'width' : 'factor';
            } elseif (($srcsetType === 'width' && isset($matches['factor'])) || ($srcsetType === 'factor' && isset($matches['width']))) {
                $this->logger->warning(sprintf('Mixed media descriptors are not valid: [%s]', implode(', ', is_array($descriptors) ? $descriptors : iterator_to_array($descriptors))), LogEnvironment::fromMethodName(__METHOD__));
                break;
            }

            if ($srcsetType === 'width') {
                $width = (int)$matches['width'];
                $scaleFactor = $width / $this->width();
                if (!$allowUpScaling && ($width / $this->baseWidth > 1)) {
                    $srcsetArray[] = $this->src() . ' ' . $this->baseWidth . 'w';
                } else {
                    $scaled = $this->scale($scaleFactor, $allowUpScaling);
                    $srcsetArray[] = $scaled->src() . ' ' . $width . 'w';
                }
            } elseif ($srcsetType === 'factor') {
                $factor = (float)$matches['factor'];
                if (
                    !$allowUpScaling && (
                        ($this->targetHeight && ($maxScaleFactor < $factor)) ||
                        ($this->targetWidth && ($maxScaleFactor < $factor))
                    )
                ) {
                    $scaled = $this->scale($maxScaleFactor, $allowUpScaling);
                    $srcsetArray[] = $scaled->src() . ' ' . $maxScaleFactor . 'x';
                } else {
                    $scaled = $this->scale($factor, $allowUpScaling);
                    $srcsetArray[] = $scaled->src() . ' ' . $factor . 'x';
                }
            }
        }

        return implode(', ', array_unique($srcsetArray));
    }
}
