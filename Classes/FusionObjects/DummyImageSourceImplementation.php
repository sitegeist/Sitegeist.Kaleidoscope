<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Sitegeist\Kaleidoscope\Domain\DummyImageSource;
use Sitegeist\Kaleidoscope\Domain\ImageSourceInterface;

class DummyImageSourceImplementation extends AbstractImageSourceImplementation
{
    /**
     * @return int|null
     */
    public function getBaseWidth(): ?int
    {
        return $this->fusionValue('baseWidth');
    }

    /**
     * @return int|null
     */
    public function getBaseHeight(): ?int
    {
        return $this->fusionValue('baseHeight');
    }

    /**
     * @return string|null
     */
    public function getBackgroundColor(): ?string
    {
        return $this->fusionValue('backgroundColor');
    }

    /**
     * @return string|null
     */
    public function getForegroundColor(): ?string
    {
        return $this->fusionValue('foregroundColor');
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->fusionValue('text');
    }

    /**
     * Create helper and initialize with the default values.
     *
     * @throws MissingActionNameException
     *
     * @return ImageSourceInterface|null
     */
    public function createHelper(): ?ImageSourceInterface
    {
        $uriBuilder = $this->runtime->getControllerContext()->getUriBuilder()->reset()->setCreateAbsoluteUri(true);
        $baseUri = $uriBuilder->uriFor('image', [], 'DummyImage', 'Sitegeist.Kaleidoscope');

        $helper = new DummyImageSource($baseUri);

        if ($baseWidth = $this->getBaseWidth()) {
            $helper->setBaseWidth($baseWidth);
        }

        if ($baseHeight = $this->getBaseHeight()) {
            $helper->setBaseHeight($baseHeight);
        }

        if ($backgroundColor = $this->getBackgroundColor()) {
            $helper->setBackgroundColor($backgroundColor);
        }

        if ($foregroundColor = $this->getForegroundColor()) {
            $helper->setForegroundColor($foregroundColor);
        }

        if ($text = $this->getText()) {
            $helper->setText($text);
        }

        if ($title = $this->getTitle()) {
            $helper->setTitle($title);
        }

        if ($alt = $this->getAlt()) {
            $helper->setAlt($alt);
        }

        return $helper;
    }
}
