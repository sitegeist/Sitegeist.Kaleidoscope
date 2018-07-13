<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Eel\ProtectedContextAwareInterface;

interface ImageSourceHelperInterface extends ProtectedContextAwareInterface
{
    public function setWidth(int $width = null) : ImageSourceHelperInterface;

    public function setHeight(int $height = null) : ImageSourceHelperInterface;

    public function applyPreset(string $name) : ImageSourceHelperInterface;

    public function scale(float $factor): ImageSourceHelperInterface;

    public function src() : string;

    public function widthSrcset(array $widthSet) : string;

    public function resolutionSrcset(array $resolutionSet) : string;

    public function getWidth() : int;

    public function getHeight() : int;

    public function __toString() : string;
}
