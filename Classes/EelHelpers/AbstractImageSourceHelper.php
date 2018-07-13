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
        $this->targetWidth = $targetWidth;
        return $this;
    }

    public function setHeight(int $targetHeight = null) : ImageSourceHelperInterface
    {
        $this->targetHeight = $targetHeight;
        return $this;
    }

    /**
     * Apply definitions from a preset to this image source
     *
     * @param string $name
     * @return ImageSourceHelperInterface
     */
    public function applyPreset(string $name) : ImageSourceHelperInterface
    {
        if ($this->thumbnailPresets) {
            $preset = Arrays::getValueByPath($this->thumbnailPresets, $name);
            if ($preset) {
                if ($width = Arrays::getValueByPath($preset, 'width')) {
                    $this->setWidth($width);
                } elseif ($width = Arrays::getValueByPath($preset, 'maximumWidth')) {
                    $this->setWidth($width);
                }
                if ($height = Arrays::getValueByPath($preset, 'height')) {
                    $this->setHeight($height);
                } elseif ($height = Arrays::getValueByPath($preset, 'maximumHeight')) {
                    $this->setHeight($height);
                }
            }
        }
    }

    /**
     * Render sourceset Attribute for various width
     *
     * @param array $widthSet
     * @return string
     */
    public function widthSrcset(array $widthSet): string
    {
        $srcsetArray = [];
        foreach ($widthSet as $targetWidth) {
            $scaleFactor = $targetWidth / $this->getWidth();
            $scaled = $this->scale($scaleFactor);
            $srcsetArray[] = $scaled->src() . ' ' . $targetWidth . 'w';
        }
        return implode(', ', $srcsetArray);
    }

    /**
     * Render sourceset Attribute for various resolution
     *
     * @param array $resolutionSet
     * @return string
     */
    public function resolutionSrcset(array $resolutionSet): string
    {
        $srcsetArray = [];
        foreach ($resolutionSet as $scaleFactor) {
            $scaled = $this->scale($scaleFactor);
            $srcsetArray[] = $scaled->src() . ' ' . $scaleFactor . 'x';
        }
        return implode(', ', $srcsetArray);
    }

    /**
     * Define wich methods are available in the eel context
     *
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (in_array($methodName, ['setWidth', 'setHeight', 'applyPreset', 'src', 'widthSrcset', 'resolutionSrcset'])) {
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
