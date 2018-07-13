<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

interface ScalableImageSourceHelperInterface extends ImageSourceHelperInterface
{
    public function scale(float $factor): ImageSourceHelperInterface;
}
