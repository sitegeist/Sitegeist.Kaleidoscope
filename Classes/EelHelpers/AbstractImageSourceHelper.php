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
     * @var mixed
     * @Flow\InjectConfiguration(path="thumbnailPresets", package="Neos.Media")
     */
    protected $thumbnailPresets;

    public function setWidth(int $targetWidth = null) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetWidth = $targetWidth;
        return $newSource;
    }

    public function setHeight(int $targetHeight = null) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
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
            \Neos\Flow\var_dump([$descriptors,$srcsetArray]);
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
        if (in_array($methodName, ['setWidth', 'setHeight', 'applyPreset', 'src', 'srcset'])) {
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
        return $this->src();
    }
}
