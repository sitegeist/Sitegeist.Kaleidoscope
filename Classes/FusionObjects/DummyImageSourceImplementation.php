<?php
namespace Sitegeist\Kaleidoscope\FusionObjects;

use Neos\Flow\Annotations as Flow;
use Sitegeist\Kaleidoscope\EelHelpers\ImageSourceHelperInterface;
use Sitegeist\Kaleidoscope\EelHelpers\DummyImageSourceHelper;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class DummyImageSourceImplementation extends AbstractImageSourceImplementation
{
    /**
     * @return mixed
     */
    public function getBaseWidth()
    {
        return $this->fusionValue('baseWidth');
    }

    /**
     * @return mixed
     */
    public function getBaseHeight()
    {
        return $this->fusionValue('baseHeight');
    }

    /**
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->fusionValue('backgroundColor');
    }

    /**
     * @return mixed
     */
    public function getForegroundColor()
    {
        return $this->fusionValue('foregroundColor');
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->fusionValue('text');
    }

    /**
     * Create helper and initialize with the default values
     *
     * @return ImageSourceHelperInterface
     */
    public function createHelper() : ImageSourceHelperInterface
    {
        $uriBuilder = $this->runtime->getControllerContext()->getUriBuilder()->reset()->setCreateAbsoluteUri(true);
        $baseUri = $uriBuilder->uriFor('image', [], 'DummyImage', 'Sitegeist.Kaleidoscope');

        $helper = new DummyImageSourceHelper($baseUri);

        if ($baseWidth = $this->getBaseWidth()) {
            $helper->setBaseWidth($baseWidth);
        };

        if ($baseHeight = $this->getBaseHeight()) {
            $helper->setBaseHeight($baseHeight);
        };

        if ($backgroundColor = $this->getBackgroundColor()) {
            $helper->setBackgroundColor($backgroundColor);
        };

        if ($foregroundColor = $this->getForegroundColor()) {
            $helper->setForegroundColor($foregroundColor);
        };

        if ($text = $this->getText()) {
            $helper->setText($text);
        };

        return $helper;
    }
}
