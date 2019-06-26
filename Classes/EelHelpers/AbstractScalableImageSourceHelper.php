<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;


abstract class AbstractScalableImageSourceHelper extends AbstractImageSourceHelper implements ScalableImageSourceHelperInterface
{
    /**
     * @var int
     */
    protected $baseWidth = null;

    /**
     * @var int
     */
    protected $baseHeight = null;

    /**
     * @param int|null $targetWidth
     * @param bool $preserveAspectRatio
     * @return ImageSourceHelperInterface
     */
    public function setWidth(int $targetWidth = null, bool $preserveAspect = false) : ImageSourceHelperInterface
    {
        $newSource = clone($this);
        $newSource->targetWidth = $targetWidth;
        if ($preserveAspect === true) {
            $aspect = ($this->targetWidth ?: $this->baseWidth) / ($this->targetHeight ?: $this->baseHeight);
            $newSource->targetHeight = round($targetWidth / $aspect);
        }
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
        if ($preserveAspect === true) {
            $aspect = ($this->targetWidth ?: $this->baseWidth) / ($this->targetHeight ?: $this->baseHeight);
            $newSource->targetWidth = round($targetHeight * $aspect);
        }
        return $newSource;
    }

    /**
     * @param float $factor
     * @return ImageSourceHelperInterface
     */
    public function scale(float $factor): ImageSourceHelperInterface
    {
        $scaledHelper = clone($this);

        if ($this->targetWidth && $this->targetHeight) {
            $scaledHelper = $scaledHelper->setDimensions(round($factor * $this->targetWidth), round($factor * $this->targetHeight));
        } elseif ($this->targetWidth) {
            $scaledHelper = $scaledHelper->setWidth(round($factor * $this->targetWidth));
        } elseif ($this->targetHeight) {
            $scaledHelper = $scaledHelper->setHeight(round($factor * $this->targetHeight));
        } else {
            $scaledHelper = $scaledHelper->setWidth(round($factor * $this->baseWidth));
        }

        return $scaledHelper;
    }

    /**
     * @return int
     */
    public function getCurrentWidth() : int
    {
        if ($this->targetWidth) {
            return $this->targetWidth;
        } elseif ($this->targetHeight) {
            return round($this->targetHeight * $this->baseWidth/ $this->baseHeight);
        } else {
            return $this->baseWidth;
        }
    }


    /**
     * @return int
     */
    public function getCurrentHeight() : int
    {
        if ($this->targetHeight) {
            return $this->targetHeight;
        } elseif ($this->targetWidth) {
            return round($this->targetWidth *  $this->baseHeight / $this->baseWidth);
        } else {
            return $this->baseHeight;
        }
    }

}
