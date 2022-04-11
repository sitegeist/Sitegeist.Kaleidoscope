<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Sitegeist\Kaleidoscope\Domain\UriImageSource;

class UriImageSourceHelper extends UriImageSource
{
    /**
     * ResourceImageSourceHelper constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        parent::__construct($uri);
    }
}
