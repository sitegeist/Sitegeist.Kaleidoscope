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
        // limit input arguments
        if ($width > 9999 || $height > 9999 ) {
            $width = 9999;
        }

        if ($height > 9999) {
            $height = 9999;
        }

        if (is_null($text)) {
            $text = (string)$width . ' x ' . (string)$height;
        }

        // create imagine
        $imagine = $this->imagineFactory->create();
        $palette = new Palette\RGB();
        $bgColor = $palette->color($backgroundColor);
        $textColor =  $palette->color($foregroundColor);

        // create image
        $imageBox = new Box($width, $height);
        $image = $imagine->create($imageBox, $bgColor);

        // render shape
        $imageAspectRatio = $width / $height;
        $baseShapeWidth = 600;
        $baseShapeHeight = 400;
        $baseShapeAspectRatio = $baseShapeWidth / $baseShapeHeight;

        $baseShape = [
            new Point(15,250), // left ground
            new Point(15,15), // left top
            new Point(585,15), // right top
            new Point(585,250), // right ground
            new Point(580,250),

            new Point(440,110), // small mountain
            new Point(360,190),  // saddle
            new Point(220,50), // big mountain

            new Point(20,250),
        ];
        $factor = ($imageAspectRatio > $baseShapeAspectRatio) ? (float)$height / (float)$baseShapeHeight : (float)$width / (float)$baseShapeWidth;
        $xoffset = ($imageAspectRatio > $baseShapeAspectRatio) ? ($width - ($baseShapeWidth * $factor)) / 2.0 : 0.0;
        $yoffset = ($imageAspectRatio < $baseShapeAspectRatio) ? ($height - ($baseShapeHeight * $factor)) / 2.0 : 0.0;

        $transformedShape = array_map(
            function(Point $point) use ($factor, $xoffset, $yoffset) {
                return new Point( $point->getX() * $factor + $xoffset, $point->getY() * $factor + $yoffset);
            },
            $baseShape
        );

        if  ($imageAspectRatio < $baseShapeAspectRatio) {
            $transformedShape[1] = new Point($transformedShape[1]->getX(), $baseShape[1]->getY() * $factor);
            $transformedShape[2] = new Point($transformedShape[2]->getX(), $baseShape[2]->getY() * $factor);
        } else {
            $transformedShape[0] = new Point($baseShape[0]->getX() * $factor , $transformedShape[0]->getY());
            $transformedShape[1] = new Point($baseShape[0]->getX() * $factor, $transformedShape[1]->getY());
            $transformedShape[2] = new Point($width - $baseShape[0]->getX() * $factor , $transformedShape[2]->getY());
            $transformedShape[3] = new Point($width - $baseShape[0]->getX() * $factor, $transformedShape[3]->getY());
        }

        $image->draw()->polygon(
            $transformedShape,
            $textColor,
            true ,
            1
        );

        // render text
        if ($text) {
            $initialFontSize = 10;
            $fontFile = $this->packageManager->getPackage('Neos.Neos')->getPackagePath() . "Resources/Public/Fonts/NotoSans/NotoSans-Regular.ttf";
            $initialFont = $imagine->font($fontFile, $initialFontSize, $textColor);

            // scale text to fit the image
            $initialFontBox = $initialFont->box($text);
            $targetFontWidth = $width * .5;
            $targetFontHeight = $height * .7;
            $correctedFontSizeByWidth = $targetFontWidth * $initialFontSize / $initialFontBox->getWidth();
            $correctedFontSizeByHeight = $targetFontHeight * $initialFontSize / $initialFontBox->getHeight();

            // render actual text
            $actualFont = $imagine->font($fontFile, min([$correctedFontSizeByWidth, $correctedFontSizeByHeight]), $textColor);
            $actualFontBox = $actualFont->box($text);
            $imageCenterPosition = new Point\Center($imageBox);
            $textCenterPosition = new Point\Center($actualFontBox);
            $centeredTextPosition = new Point($imageCenterPosition->getX() - $textCenterPosition->getX(), ($height * .73 - $actualFontBox->getHeight() *.5  ));
            $image->draw()->text($text, $actualFont, $centeredTextPosition);
        }

        // build result
        $this->response->setHeader( 'Cache-Control', 'max-age=883000000');
        $this->response->setHeader( 'Content-type', 'image/png');
        return $image->get('png');
    }

}