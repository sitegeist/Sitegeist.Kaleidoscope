<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

class ResourceImageSource extends AbstractImageSource
{
    /**
     * @Flow\Inject
     *
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string|null
     */
    protected $package;

    /**
     * ResourceImageSourceHelper constructor.
     *
     * @param string|null $package
     * @param string      $path
     */
    public function __construct(?string $package, string $path)
    {
        $this->package = $package;
        $this->path = $path;
    }

    public function src(): string
    {
        if ($this->package) {
            return $this->resourceManager->getPublicPackageResourceUri($this->package, $this->path);
        }

        return $this->resourceManager->getPublicPackageResourceUriByPath($this->path);
    }
}
