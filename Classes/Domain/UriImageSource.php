<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;

class UriImageSource extends AbstractImageSource
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
    protected $uri;

    /**
     * @param string      $uri
     * @param string|null $title
     * @param string|null $alt
     */
    public function __construct(string $uri, ?string $title = null, ?string $alt = null)
    {
        parent::__construct($title, $alt);
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function src(): string
    {
        return $this->uri;
    }
}
