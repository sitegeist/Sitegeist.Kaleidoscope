<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Utility\Arrays;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractImageSourceHelper
 *
 * @package Sitegeist\Kaleidoscope\EelHelpers
 */
abstract class AbstractImageSourceHelper implements ImageSourceHelperInterface
{
    /**
     * @var int
     */
    protected $targetWidth;

    /**
     * @var int
     */
    protected $targetHeight;

    /**
     * @var string
     */
    protected $targetFormat;

    /**
     * @var array
     */
    protected $targetImageVariant = [];

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="thumbnailPresets", package="Neos.Media")
     */
    protected $thumbnailPresets;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="variantPresets", package="Neos.Media")
     */
    protected $variantPresets;

    /**
     * @param int|null $targetWidth
     * @param bool $preserveAspect
     * @return ImageSourceHelperInterface
     */
    public function setWidth(int $targetWidth = null, bool $preserveAspect = false): ImageSourceHelperInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
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
        return $newSource;
    }

    /**
     * @param string|null $format
     * @return ImageSourceHelperInterface
     */
    public function setFormat(string $format = null): ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetFormat = $format;
        return $newSource;
    }

    /**
     * @param int|null $targetWidth
     * @param int|null $targetHeight
     * @return ImageSourceHelperInterface
     */
    public function setDimensions(int $targetWidth = null, int $targetHeight = null): ImageSourceHelperInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
        $newSource->targetHeight = $targetHeight;
        return $newSource;
    }

    /**
     * DEPRECATED Apply definitions from a thumbnail preset to this image source
     *
     * @param string $name
     * @deprecated use applyThumbnailPreset
     * @return ImageSourceHelperInterface
     */
    public function applyPreset(string $name): ImageSourceHelperInterface
    {
        return $this->applyThumbnailPreset($name);
    }

    /**
     * Apply definitions from a thumbnail preset to this image source
     *
     * @param string $name
     * @return ImageSourceHelperInterface
     */
    public function applyThumbnailPreset(string $name): ImageSourceHelperInterface
    {
        $newSource = clone $this;
        if (isset($this->thumbnailPresets[$name])) {
            $preset = $this->thumbnailPresets[$name];
            if ($width = $preset['width'] ?? null) {
                $newSource = $newSource->setWidth($width);
            } elseif ($width = $preset['maximumWidth'] ?? null) {
                $newSource = $newSource->setWidth($width);
            }
            if ($height = $preset['height'] ?? null) {
                $newSource = $newSource->setHeight($height);
            } elseif ($height = $preset['maximumHeight'] ?? null) {
                $newSource = $newSource->setHeight($height);
            }
        } else {
            $this->logger->warning(sprintf('Thumbnail preset "%s" is not configured', $name), LogEnvironment::fromMethodName(__METHOD__));
        }
        return $newSource;
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
        if (!isset($this->variantPresets[$presetIdentifier]['variants'][$presetVariantName])) {
            $this->logger->warning(sprintf('Variant "%s" of preset "%s" is not configured', $presetVariantName, $presetIdentifier), LogEnvironment::fromMethodName(__METHOD__));
        }

        $newSource = clone $this;
        $newSource->targetImageVariant = ['presetIdentifier' => $presetIdentifier, 'presetVariantName' => $presetVariantName];
        return $newSource;
    }

    /**
     * Render sourceset Attribute for various media descriptors
     *
     * @param mixed $mediaDescriptors
     * @return string
     */
    public function srcset($mediaDescriptors): string
    {
        if ($this instanceof ScalableImageSourceHelperInterface) {
            $srcsetArray = [];

            if (is_array($mediaDescriptors) || $mediaDescriptors instanceof \Traversable) {
                $descriptors = $mediaDescriptors;
            } else {
                $descriptors = Arrays::trimExplode(',', (string)$mediaDescriptors);
            }

            foreach ($descriptors as $descriptor) {
                if (preg_match('/^(?<width>[0-9]+)w$/u', $descriptor, $matches)) {
                    $width = (int)$matches['width'];
                    $scaleFactor = $width / $this->getCurrentWidth();
                    $scaled = $this->scale($scaleFactor);
                    $srcsetArray[] = $scaled->src() . ' ' . $width . 'w';
                } elseif (preg_match('/^(?<factor>[0-9\\.]+)x$/u', $descriptor, $matches)) {
                    $factor = (float)$matches['factor'];
                    $scaled = $this->scale($factor);
                    $srcsetArray[] = $scaled->src() . ' ' . $factor . 'x';
                }
            }
            return implode(', ', $srcsetArray);
        }

        return $this->src();
    }

    /**
     * Define which methods are available in the Eel context
     *
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (in_array($methodName, ['setWidth', 'setHeight', 'setDimensions', 'setFormat', 'applyPreset', 'applyThumbnailPreset', 'useVariantPreset', 'src', 'srcset'])) {
            return true;
        }

        return false;
    }

    /**
     * If the source is cast to string the default source is returned
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->src();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
