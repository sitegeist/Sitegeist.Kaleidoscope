<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Sitegeist\Kaleidoscope\Domain\ResourceImageSource;

/**
 * @deprecated use Sitegeist\Kaleidoscope\Domain\ResourceImageSource;
 */
class ResourceImageSourceHelper extends ResourceImageSource
{
    /**
     * ResourceImageSourceHelper constructor.
     *
     * @param string|null $package
     * @param string      $path
     */
    public function __construct(?string $package, string $path)
    {
        parent::__construct($package, $path);
    }
}
