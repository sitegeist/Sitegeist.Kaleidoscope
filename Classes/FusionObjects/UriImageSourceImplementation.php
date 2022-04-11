<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;
use Sitegeist\Kaleidoscope\Domain\UriImageSource;

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
     * @return ImageSourceInterface|null
     */
    public function evaluate(): ?ImageSourceInterface
    {
        if ($uri = $this->getUri()) {
            return new UriImageSource(
                $uri,
                $this->getTitle(),
                $this->getAlt()
            );
        } else {
            return null;
        }
    }
}
