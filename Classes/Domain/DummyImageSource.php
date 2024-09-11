<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\ServerRequestAttributes;
use Neos\Flow\Mvc\ActionRequestFactory;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class DummyImageSource extends AbstractScalableImageSource
{
    /**
     * @var DummyImageGenerator
     *
     * @Flow\Inject
     */
    protected $dummyImageGenerator;

    /**
     * @var UriFactoryInterface
     * @Flow\Inject
     */
    protected $uriFactory;

    /**
     * @var ServerRequestFactoryInterface
     * @Flow\Inject
     */
    protected $serverRequestFactory;

    /**
     * @var ActionRequestFactory
     * @Flow\Inject
     */
    protected $actionRequestFactory;

    /**
     * @var string
     */
    protected $backgroundColor;

    /**
     * @var string
     */
    protected $foregroundColor;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string|null
     */
    protected $baseUri;

    /**
     * @param string|null $baseUri
     * @param string|null $title
     * @param string|null $alt
     * @param int|null    $baseWidth
     * @param int|null    $baseHeight
     * @param string|null $backgroundColor
     * @param string|null $foregroundColor
     * @param string|null $text
     */
    public function __construct(?string $baseUri, ?string $title = null, ?string $alt = null, ?int $baseWidth = null, ?int $baseHeight = null, ?string $backgroundColor = null, ?string $foregroundColor = null, ?string $text = null)
    {
        parent::__construct($title, $alt);
        $this->baseUri = $baseUri;
        $this->baseWidth = $baseWidth ?? 600;
        $this->baseHeight = $baseHeight ?? 400;
        $this->backgroundColor = $backgroundColor ?? '999';
        $this->foregroundColor = $foregroundColor ?? 'fff';
        $this->text = $text ?? '';
    }

    /**
     * Use the variant generated from the given variant preset in this image source.
     *
     * @param string $presetIdentifier
     * @param string $presetVariantName
     *
     * @return ImageSourceInterface
     */
    public function withVariantPreset(string $presetIdentifier, string $presetVariantName): ImageSourceInterface
    {
        /** @var DummyImageSource $newSource */
        $newSource = parent::withVariantPreset($presetIdentifier, $presetVariantName);

        if ($newSource->targetImageVariant !== []) {
            $targetBox = $this->estimateDimensionsFromVariantPresetAdjustments($presetIdentifier, $presetVariantName);
            $newSource->baseWidth = $targetBox->getWidth();
            $newSource->baseHeight = $targetBox->getHeight();
        }

        return $newSource;
    }

    /**
     * @return string
     */
    public function src(): string
    {
        if (is_string($this->baseUri)) {
            $baseUri = $this->baseUri;
        } else {
            $uri = $this->uriFactory->createUri('http://localhost');

            $httpRequest = $this->serverRequestFactory->createServerRequest('GET', $uri)
                ->withAttribute(
                    ServerRequestAttributes::ROUTING_PARAMETERS,
                    RouteParameters::createEmpty()->withParameter('requestUriHost', $uri->getHost())
                );

            $uriBuilder = new UriBuilder();
            $uriBuilder->setRequest($this->actionRequestFactory->createActionRequest($httpRequest));
            $uriBuilder->setFormat('html');
            $uriBuilder->setCreateAbsoluteUri(false);

            $baseUri = $uriBuilder->uriFor('image', [], 'DummyImage', 'Sitegeist.Kaleidoscope');
        }

        $arguments = [
            'w' => $this->getCurrentWidth(),
            'h' => $this->getCurrentHeight(),
        ];

        if ($this->backgroundColor) {
            $arguments['bg'] = $this->backgroundColor;
        }

        if ($this->foregroundColor) {
            $arguments['fg'] = $this->foregroundColor;
        }

        if ($this->text) {
            $arguments['t'] = $this->text;
        }

        if ($this->targetFormat) {
            $arguments['f'] = $this->targetFormat;
        }

        return $baseUri . '?' . http_build_query($arguments);
    }

    public function dataSrc(): string
    {
        $w = $this->getCurrentWidth() ?: 600;
        $h = $this->getCurrentHeight() ?: 400;

        $bg = $this->backgroundColor ?: '#000';
        $fg = $this->foregroundColor ?: '#fff';
        $t = $this->text ?: null;
        $f = $this->targetFormat ?: 'png';

        $dummyImage = $this->dummyImageGenerator->createDummyImage($w, $h, $bg, $fg, $t, $f);
        if ($dummyImage) {
            return 'data:image/' . $f . ';base64,' . base64_encode($dummyImage->get($f));
        }

        return '';
    }
}
