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
     * @param string|null $package
     * @param string      $path
     * @param string|null $title
     * @param string|null $alt
     */
    public function __construct(?string $package, string $path, ?string $title = null, ?string $alt = null)
    {
        parent::__construct($title, $alt);
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
