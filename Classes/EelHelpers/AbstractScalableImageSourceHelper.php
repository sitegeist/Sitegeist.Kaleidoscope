<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\ValueObject\Configuration\VariantPreset;
use Neos\Media\Domain\Model\Adjustment\ImageAdjustmentInterface;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\Model\Adjustment\CropImageAdjustment;
use Neos\Media\Domain\ValueObject\Configuration\Adjustment;
use Neos\Utility\ObjectAccess;

abstract class AbstractScalableImageSourceHelper extends AbstractImageSourceHelper implements ScalableImageSourceHelperInterface
{
    /**
     * @var ImagineInterface
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
     * @param int|null $targetWidth
     * @param bool $preserveAspect
     * @return ImageSourceHelperInterface
     */
    public function setWidth(int $targetWidth = null, bool $preserveAspect = false): ImageSourceHelperInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
        if ($preserveAspect === true) {
            $aspect = ($this->targetWidth ?: $this->baseWidth) / ($this->targetHeight ?: $this->baseHeight);
            $newSource->targetHeight = (int)round($targetWidth / $aspect);
        }
        return $newSource;
    }

    /**
     * @param int|null $targetHeight
     * @param bool $preserveAspect
     * @return ImageSourceHelperInterface
     */
    public function setHeight(int $targetHeight = null, bool $preserveAspect = false): ImageSourceHelperInterface
    {
        $newSource = clone $this;
        $newSource->targetHeight = $targetHeight;
        if ($preserveAspect === true) {
            $aspect = ($this->targetWidth ?: $this->baseWidth) / ($this->targetHeight ?: $this->baseHeight);
            $newSource->targetWidth = (int)round($targetHeight * $aspect);
        }
        return $newSource;
    }

    /**
     * @param float $factor
     * @return ImageSourceHelperInterface
     */
    public function scale(float $factor): ImageSourceHelperInterface
    {
        $scaledHelper = clone $this;

        if ($this->targetWidth && $this->targetHeight) {
            $scaledHelper = $scaledHelper->setDimensions((int)round($factor * $this->targetWidth), (int)round($factor * $this->targetHeight));
        } elseif ($this->targetWidth) {
            $scaledHelper = $scaledHelper->setWidth((int)round($factor * $this->targetWidth));
        } elseif ($this->targetHeight) {
            $scaledHelper = $scaledHelper->setHeight((int)round($factor * $this->targetHeight));
        } else {
            $scaledHelper = $scaledHelper->setWidth((int)round($factor * $this->baseWidth));
        }

        return $scaledHelper;
    }

    /**
     * @return int
     */
    public function getCurrentWidth(): int
    {
        if ($this->targetWidth) {
            return $this->targetWidth;
        }

        if ($this->targetHeight) {
            return (int)round($this->targetHeight * $this->baseWidth / $this->baseHeight);
        }

        return $this->baseWidth;
    }

    /**
     * @return int
     */
    public function getCurrentHeight(): int
    {
        if ($this->targetHeight) {
            return $this->targetHeight;
        }

        if ($this->targetWidth) {
            return (int)round($this->targetWidth * $this->baseHeight / $this->baseWidth);
        }

        return $this->baseHeight;
    }

    /**
     * @param string $presetIdentifier
     * @param string $presetVariantName
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
                case ($adjustment instanceof ResizeImageAdjustment):
                    $image = $this->imagineService->create($imageBox);
                    if ($adjustment->canBeApplied($image)) {
                        $image = $adjustment->applyToImage($image);
                        return new Box(
                            (int)round($image->getSize()->getWidth()),
                            (int)round($image->getSize()->getHeight())
                        );
                    }
                    break;
                case ($adjustment instanceof CropImageAdjustment):
                    $desiredAspectRatio = $adjustment->getAspectRatio();
                    if ($desiredAspectRatio !== null) {
                        [, , $newWidth, $newHeight] = CropImageAdjustment::calculateDimensionsByAspectRatio($this->baseWidth, $this->baseHeight, $desiredAspectRatio);
                    } else {
                        $newWidth = $adjustment->getWidth();
                        $newHeight = $adjustment->getHeight();
                    }
                    return new Box(
                        (int)round($newWidth),
                        (int)round($newHeight)
                    );
                    break;
            }
        }

        return $imageBox;
    }

    /**
     * @param Adjustment $adjustmentConfiguration
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
}
