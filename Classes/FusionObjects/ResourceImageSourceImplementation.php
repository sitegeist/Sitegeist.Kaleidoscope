<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\ResourceImageSourceHelper;

class ResourceImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->fusionValue('package');
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->fusionValue('path');
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->fusionValue('title');
    }

    /**
     * @return string|null
     */
    public function getAlt(): ?string
    {
        return $this->fusionValue('alt');
    }

    /**
     * Create helper and initialize with the default values.
     *
     * @return ImageSourceHelperInterface|null
     */
    public function evaluate(): ?ImageSourceHelperInterface
    {
        if ($path = $this->getPath()) {
            $helper = new ResourceImageSourceHelper($this->getPackage(), $path);
        } else {
            return null;
        }

        if ($title = $this->getTitle()) {
            $helper->setTitle($title);
        }

        if ($alt = $this->getAlt()) {
            $helper->setAlt($alt);
        }

        return $helper;
    }
}
