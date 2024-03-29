<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;
use Sitegeist\Kaleidoscope\Domain\ResourceImageSource;

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
     */
    public function evaluate(): ?ImageSourceInterface
    {
        if ($path = $this->getPath()) {
            return new ResourceImageSource(
                $this->getPackage(),
                $path,
                $this->getTitle(),
                $this->getAlt()
            );
        } else {
            return null;
        }
    }
}
