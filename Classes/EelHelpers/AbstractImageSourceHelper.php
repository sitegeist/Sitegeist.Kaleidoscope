<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Arrays;

/**
 * Class AbstractImageSourceHelper
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
     * @var string|null
     */
    protected $targetFormat = null;

    /**
     * @var mixed
     * @Flow\InjectConfiguration(path="thumbnailPresets", package="Neos.Media")
     */
    protected $thumbnailPresets;

    /**
     * @param int|null $targetWidth
     * @param bool $preserveAspectRatio
     * @return ImageSourceHelperInterface
     */
    public function setWidth(int $targetWidth = null, bool $preserveAspect = false) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetWidth = $targetWidth;
        return $newSource;
    }

    /**
     * @param int|null $targetHeight
     * @param bool $preserverAspectRatio
     * @return ImageSourceHelperInterface
     */
    public function setHeight(int $targetHeight = null, bool $preserveAspect = false) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetHeight = $targetHeight;
        return $newSource;
    }

    /**
     * @param string|null $format
     * @return ImageSourceHelperInterface
     */
    public function setFormat(?string $format = null) : ImageSourceHelperInterface
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
    public function setDimensions(int $targetWidth = null, int $targetHeight = null) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetWidth = $targetWidth;
        $newSource->targetHeight = $targetHeight;
        return $newSource;
    }

    /**
     * Apply definitions from a preset to this image source
     *
     * @param string $name
     * @return ImageSourceHelperInterface
     */
    public function applyPreset(string $name) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        if ($this->thumbnailPresets) {
            $preset = Arrays::getValueByPath($this->thumbnailPresets, $name);
            if ($preset) {
                if ($width = Arrays::getValueByPath($preset, 'width')) {
                    $newSource->setWidth($width);
                } elseif ($width = Arrays::getValueByPath($preset, 'maximumWidth')) {
                    $newSource->setWidth($width);
                }
                if ($height = Arrays::getValueByPath($preset, 'height')) {
                    $newSource->setHeight($height);
                } elseif ($height = Arrays::getValueByPath($preset, 'maximumHeight')) {
                    $newSource->setHeight($height);
                }
            }
        }
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
                    $width = (int) $matches['width'];
                    $scaleFactor = $width / $this->getCurrentWidth();
                    $scaled = $this->scale($scaleFactor);
                    $srcsetArray[] = $scaled->src() . ' ' . $width . 'w';
                } elseif (preg_match('/^(?<factor>[0-9\\.]+)x$/u', $descriptor, $matches)){
                    $factor = (float) $matches['factor'];
                    $scaled = $this->scale($factor);
                    $srcsetArray[] = $scaled->src() . ' ' . $factor . 'x';
                }
            }
            return implode(', ', $srcsetArray);
        } else {
            return $this->src();
        }
    }

    /**
     * Define wich methods are available in the eel context
     *
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (in_array($methodName, ['setWidth', 'setHeight', 'setDimensions', 'setFormat', 'applyPreset', 'src', 'srcset'])) {
            return true;
        } else {
            return false;
        }
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
