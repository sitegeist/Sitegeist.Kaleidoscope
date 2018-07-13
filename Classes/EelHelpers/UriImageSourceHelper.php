<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

class UriImageSourceHelper extends AbstractImageSourceHelper
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    protected $uri = null;

    /**
     * ResourceImageSourceHelper constructor.
     * @param string $package
     * @param string $path
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function scale(float $factor): ImageSourceHelperInterface
    {
        return $this;
    }

    public function src(): string
    {
        return $this->uri;
    }
}
