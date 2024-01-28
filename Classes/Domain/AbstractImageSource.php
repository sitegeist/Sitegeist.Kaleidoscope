<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Utility\Arrays;
use Psr\Log\LoggerInterface;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;

/**
 * Class AbstractImageSourceHelper.
 */
abstract class AbstractImageSource implements ImageSourceInterface, ProtectedContextAwareInterface, ImageSourceHelperInterface
{
    /**
     * @var int|null
     */
    protected $targetWidth;

    /**
     * @var int|null
     */
    protected $targetHeight;

    /**
     * @var int|null
     */
    protected $targetQuality;

    /**
     * @var string|null
     */
    protected $targetFormat;

    /**
     * @var string[]
     */
    protected $targetImageVariant = [];

    /**
     * @Flow\Inject
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var mixed[]
     *
     * @Flow\InjectConfiguration(path="thumbnailPresets", package="Neos.Media")
     */
    protected $thumbnailPresets;

    /**
     * @var mixed[]
     *
     * @Flow\InjectConfiguration(path="variantPresets", package="Neos.Media")
     */
    protected $variantPresets;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $alt;

    public function __construct(?string $title = null, ?string $alt = null)
    {
        $this->title = $title;
        $this->alt = $alt;
    }

    /**
     * @deprecated
     */
    public function setWidth(int $targetWidth, bool $preserveAspect = false): ImageSourceInterface
    {
        return $this->withWidth($targetWidth, $preserveAspect);
    }

    public function withWidth(int $targetWidth, bool $preserveAspect = false): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;

        return $newSource;
    }

    /**
     * @deprecated
     */
    public function setHeight(int $targetHeight, bool $preserveAspect = false): ImageSourceInterface
    {
        return $this->withHeight($targetHeight, $preserveAspect);
    }

    public function withHeight(int $targetHeight, bool $preserveAspect = false): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetHeight = $targetHeight;

        return $newSource;
    }

    /**
     * @deprecated
     */
    public function setQuality(int $quality): ImageSourceInterface
    {
        return $this->withQuality($quality);
    }

    public function withQuality(int $quality): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetQuality = $quality;

        return $newSource;
    }

    /**
     * @deprecated
     */
    public function setFormat(string $format): ImageSourceInterface
    {
        return $this->withFormat($format);
    }

    public function withFormat(string $format): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetFormat = $format;

        return $newSource;
    }

    /**
     * @deprecated
     */
    public function setDimensions(int $targetWidth, int $targetHeight): ImageSourceInterface
    {
        return $this->withDimensions($targetWidth, $targetHeight);
    }

    public function withDimensions(int $targetWidth, int $targetHeight): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->targetWidth = $targetWidth;
        $newSource->targetHeight = $targetHeight;

        return $newSource;
    }

    /**
     * @deprecated use applyThumbnailPreset
     */
    public function applyPreset(string $name): ImageSourceInterface
    {
        return $this->withThumbnailPreset($name);
    }

    /**
     * @deprecated
     */
    public function applyThumbnailPreset(string $name): ImageSourceInterface
    {
        return $this->withThumbnailPreset($name);
    }

    public function withThumbnailPreset(string $name): ImageSourceInterface
    {
        $newSource = clone $this;
        if (isset($this->thumbnailPresets[$name])) {
            $preset = $this->thumbnailPresets[$name];
            if ($width = $preset['width'] ?? null) {
                $newSource = $newSource->withWidth($width);
            } elseif ($width = $preset['maximumWidth'] ?? null) {
                $newSource = $newSource->withWidth($width);
            }
            if ($height = $preset['height'] ?? null) {
                $newSource = $newSource->withHeight($height);
            } elseif ($height = $preset['maximumHeight'] ?? null) {
                $newSource = $newSource->withHeight($height);
            }
        } else {
            $this->logger->warning(sprintf('Thumbnail preset "%s" is not configured', $name), LogEnvironment::fromMethodName(__METHOD__));
        }

        return $newSource;
    }

    /**
     * @deprecated
     */
    public function useVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface
    {
        return $this->withVariantPreset($presetIdentifier, $presetVariantName);
    }

    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface
    {
        if (!isset($this->variantPresets[$presetIdentifier]['variants'][$presetVariantName])) {
            $this->logger->warning(sprintf('Variant "%s" of preset "%s" is not configured', $presetVariantName, $presetIdentifier), LogEnvironment::fromMethodName(__METHOD__));
        }

        $newSource = clone $this;
        $newSource->targetImageVariant = ['presetIdentifier' => $presetIdentifier, 'presetVariantName' => $presetVariantName];

        return $newSource;
    }

    /**
     * Render sourceset Attribute for various media descriptors.
     *
     * @param mixed $mediaDescriptors
     *
     * @return string
     */
    public function srcset($mediaDescriptors): string
    {
        if ($this instanceof ScalableImageSourceInterface) {
            $srcsetArray = [];

            if (is_array($mediaDescriptors) || $mediaDescriptors instanceof \Traversable) {
                $descriptors = $mediaDescriptors;
            } else {
                $descriptors = Arrays::trimExplode(',', (string) $mediaDescriptors);
            }

            foreach ($descriptors as $descriptor) {
                if (preg_match('/^(?<width>[0-9]+)w$/u', $descriptor, $matches)) {
                    $width = (int) $matches['width'];
                    $scaleFactor = $width / $this->width();
                    $scaled = $this->scale($scaleFactor);
                    $srcsetArray[] = $scaled->src() . ' ' . $width . 'w';
                } elseif (preg_match('/^(?<factor>[0-9\\.]+)x$/u', $descriptor, $matches)) {
                    $factor = (float) $matches['factor'];
                    $scaled = $this->scale($factor);
                    $srcsetArray[] = $scaled->src() . ' ' . $factor . 'x';
                }
            }

            return implode(', ', $srcsetArray);
        }

        return $this->src();
    }

    /**
     * @deprecated use withTitle
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function withTitle(?string $title): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->title = $title;

        return $newSource;
    }

    /**
     * @deprecated use withAlt
     */
    public function setAlt(?string $alt): void
    {
        $this->alt = $alt;
    }

    public function withAlt(?string $alt): ImageSourceInterface
    {
        $newSource = clone $this;
        $newSource->alt = $alt;

        return $newSource;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function alt(): ?string
    {
        return $this->alt;
    }

    public function width(): ?int
    {
        return null;
    }

    public function height(): ?int
    {
        return null;
    }

    /**
     * Define which methods are available in the Eel context.
     *
     * @param string $methodName
     *
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (
            in_array(
                $methodName,
                [
                'withAlt',
                'withTitle',
                'withDimensions',
                'withFormat',
                'withQuality',
                'withWidth',
                'withHeight',
                'withThumbnailPreset',
                'withVariantPreset',

                'setWidth',
                'setHeight',
                'setDimensions',
                'setFormat',
                'setQuality',
                'applyPreset',
                'applyThumbnailPreset',
                'useVariantPreset',

                'src',
                'dataSrc',
                'srcset',
                'title',
                'alt',
                'width',
                'height',
                ]
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * If the source is cast to string the default source is returned.
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
