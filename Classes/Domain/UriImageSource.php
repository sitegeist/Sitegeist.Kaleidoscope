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

    public function dataSrc(): string
    {
        $content = file_get_contents($this->uri);
        if ($content) {
            $extension = pathinfo($this->uri, PATHINFO_EXTENSION);

            return 'data:image/'.$extension.';base64,'.base64_encode($content);
        } else {
            return '';
        }
    }
}
