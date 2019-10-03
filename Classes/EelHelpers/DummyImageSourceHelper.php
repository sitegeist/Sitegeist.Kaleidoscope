<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Adjustment\CropImageAdjustment;
use Neos\Media\Domain\Model\Adjustment\ImageAdjustmentInterface;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\ValueObject\Configuration\Adjustment;
use Neos\Media\Domain\ValueObject\Configuration\VariantPreset;
use Neos\Utility\ObjectAccess;

class DummyImageSourceHelper extends AbstractScalableImageSourceHelper
{
    /**
     * @var ImagineInterface
     * @Flow\Inject
     */
    protected $imagineService;

    /**
     * @var string
     */
    protected $backgroundColor = '999';

    /**
     * @var string
     */
    protected $foregroundColor = 'fff';

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $baseUri = '';

    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        $this->baseWidth = 600;
        $this->baseHeight = 400;
        $this->baseUri = $baseUri;
    }

    /**
     * @param int $baseWidth
     */
    public function setBaseWidth(int $baseWidth): void
    {
        $this->baseWidth = $baseWidth;
    }

    /**
     * @param int $baseHeight
     */
    public function setBaseHeight(int $baseHeight): void
    {
        $this->baseHeight = $baseHeight;
    }

    /**
     * @param string $backgroundColor
     */
    public function setBackgroundColor(string $backgroundColor): void
    {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * @param string $foregroundColor
     */
    public function setForegroundColor(string $foregroundColor): void
    {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * @param null $text
     */
    public function setText($text): void
    {
        $this->text = $text;
    }

    /**
     * Use the variant generated from the given variant preset in this image source
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     * @return ImageSourceHelperInterface
     */
    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceHelperInterface
    {
        /** @var DummyImageSourceHelper $newSource */
        $newSource = parent::useVariantPreset($presetIdentifier, $presetVariantName);

        if ($newSource->targetImageVariant !== []) {
            $newSource = $this->calculateSizeFromVariantPresetAdjustments($newSource);
        }

        return $newSource;
    }

    /**
     * @return string
     */
    public function src(): string
    {
        $arguments = [
            'w' => $this->getCurrentWidth(),
            'h' => $this->getCurrentHeight()
        ];

        if ($this->backgroundColor) {
            $arguments['bg'] = $this->backgroundColor;
        }

        if ($this->foregroundColor) {
            $arguments['fg'] = $this->foregroundColor;
        }

        if ($this->text) {
            $arguments['t'] = $this->text;
        }

        if ($this->targetFormat) {
            $arguments['f'] = $this->targetFormat;
        }

        return $this->baseUri . '?' . http_build_query($arguments);
    }

    /**
     * @param DummyImageSourceHelper $source
     * @return ImageSourceHelperInterface
     * @throws \RuntimeException
     */
    protected function calculateSizeFromVariantPresetAdjustments(DummyImageSourceHelper $source): ImageSourceHelperInterface
    {
        $presetIdentifier = $source->targetImageVariant['presetIdentifier'];
        $presetVariantName = $source->targetImageVariant['presetVariantName'];

        $assetVariantPreset = VariantPreset::fromConfiguration($this->variantPresets[$presetIdentifier]);
        foreach ($assetVariantPreset->variants()[$presetVariantName]->adjustments() as $adjustmentConfiguration) {
            $adjustment = $this->createAdjustment($adjustmentConfiguration);

            switch (true) {
                case ($adjustment instanceof ResizeImageAdjustment):
                    $imageBox = new Box($source->baseWidth, $$source->baseHeight);
                    $image = $this->imagineService->create($imageBox);
                    if ($adjustment->canBeApplied($image)) {
                        $image = $adjustment->applyToImage($image);
                        $source->baseWidth = $image->getSize()->getWidth();
                        $source->baseHeight = $image->getSize()->getHeight();
                    }
                break;
                case ($adjustment instanceof CropImageAdjustment):
                    $desiredAspectRatio = $adjustment->getAspectRatio();
                    if ($desiredAspectRatio !== null) {
                        [, , $newWidth, $newHeight] = CropImageAdjustment::calculateDimensionsByAspectRatio($source->baseWidth, $source->baseHeight, $desiredAspectRatio);
                    } else {
                        $newWidth = $adjustment->getWidth();
                        $newHeight = $adjustment->getHeight();
                    }

                    $source->baseWidth = $newWidth;
                    $source->baseHeight = $newHeight;
                break;
            }
        }

        return $source;
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
