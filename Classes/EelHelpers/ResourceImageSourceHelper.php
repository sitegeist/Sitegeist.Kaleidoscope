<?php
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

    protected $path = null;

    protected $package = null;

    /**
     * ResourceImageSourceHelper constructor.
     * @param string $package
     * @param string $path
     */
    public function __construct(string $package, string $path)
    {
        $this->package = $package;
        $this->path = $path;
    }

    public function scale(float $factor): ImageSourceHelperInterface
    {
        return $this;
    }

    public function src(): string
    {
        return $this->resourceManager->getPublicPackageResourceUri($this->package, $this->path);
    }
}
