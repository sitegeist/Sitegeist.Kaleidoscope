<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\UriImageSourceHelper;

class UriImageSourceImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->fusionValue('uri');
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
        if ($uri = $this->getUri()) {
            $helper = new UriImageSourceHelper($uri);
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
