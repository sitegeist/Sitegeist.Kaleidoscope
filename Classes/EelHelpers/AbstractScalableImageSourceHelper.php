<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

abstract class AbstractScalableImageSourceHelper extends AbstractImageSourceHelper implements ScalableImageSourceHelperInterface
{
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

}
