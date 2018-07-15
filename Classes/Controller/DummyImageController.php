<?php
namespace Sitegeist\Kaleidoscope\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Imagine\ImagineFactory;

use Imagine\Image\Palette;
use Imagine\Image\Box;
use Imagine\Image\Point;

class DummyImageController extends ActionController
{
    /**
     * @var ImagineFactory
     * @Flow\Inject
     */
    protected $imagineFactory;

    /**
     * @var ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @var PackageManagerInterface
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * Get a dummy-image
     *
     * @param int $width
     * @param int $height
     * @param string $backgroundColor
     * @param string $foregroundColor
     * @param string $text
     */
    public function imageAction (int $width = 600, int $height = 400, string $backgroundColor = '#000', string $foregroundColor = '#fff', string $text = null)
    {
        if ($width > 9999 || $height > 9999 ) {
            $width = 9999;
        }

        if ($height > 9999) {
            $height = 9999;
        }

        if (!$text) {
            $text = (string)$width . ' x ' . (string)$height;
        }

        $imagine = $this->imagineFactory->create();
        $palette = new Palette\RGB();

        // create image
        $imageBox = new Box($width, $height);
        $image = $imagine->create($imageBox, $palette->color($backgroundColor));

        // render text
        $initialFontSize = 10;
        $fontFile = $this->packageManager->getPackage('Neos.Neos')->getPackagePath()."Resources/Public/Fonts/NotoSans/NotoSans-Regular.ttf";
        $initialFont = $imagine->font($fontFile , $initialFontSize, $palette->color($foregroundColor));

        // scale text to fit the image
        $initialFontBox = $initialFont->box($text);
        $targetFontWidth = $width * .5;
        $targetFontHeight = $height * .7;
        $correctedFontSizeByWidth = $targetFontWidth * $initialFontSize / $initialFontBox->getWidth();
        $correctedFontSizeByHeight = $targetFontHeight * $initialFontSize / $initialFontBox->getHeight();

        // render actual text
        $actualFont = $imagine->font($fontFile , min([$correctedFontSizeByWidth, $correctedFontSizeByHeight]), $palette->color($foregroundColor));
        $actualFontBox = $actualFont->box($text);
        $imageCenterPosition = new Point\Center($imageBox);
        $textCenterPosition = new Point\Center($actualFontBox);
        $centeredTextPosition = new Point($imageCenterPosition->getX() - $textCenterPosition->getX(), $imageCenterPosition->getY() - $textCenterPosition->getY());
        $image->draw()->text($text, $actualFont, $centeredTextPosition);

        // build result
        $this->response->setHeader( 'Content-type', 'image/png');
        return $image->get('png');
    }

}