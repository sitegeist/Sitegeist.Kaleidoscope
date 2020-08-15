<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

class ResourceImageSourceHelper extends AbstractImageSourceHelper
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $package;

    /**
     * ResourceImageSourceHelper constructor.
     *
     * @param string $package
     * @param string $path
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
