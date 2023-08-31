<?php

declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\ResourceManagement\ResourceManager;
use Psr\Log\LoggerInterface;
use Sitegeist\Kaleidoscope\Domain\DummyImageGenerator;
use Imagine\Image\ImageInterface;

class DummyImageController extends ActionController
{
    /**
     * @var DummyImageGenerator
     *
     * @Flow\Inject
     */
    protected $dummyImageService;

    /**
     * @var ResourceManager
     *
     * @Flow\Inject
     */
    protected $resourceManager;

    /**
     * @var PackageManager
     *
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Flow\InjectConfiguration
     *
     * @var mixed[]
     */
    protected $settings;

    /**
     * Get a dummy-image.
     *
     * @param int         $w
     * @param int         $h
     * @param string      $bg
     * @param string      $fg
     * @param string|null $t
     * @param string      $f
     *
     * @return string
     */
    public function imageAction(int $w = 600, int $h = 400, string $bg = '#000', string $fg = '#fff', string $t = null, string $f = 'png'): string
    {
        try {
            $dummyImage = $this->dummyImageService->createDummyImage($w, $h, $bg, $fg, $t, $f);

            if ($dummyImage instanceof ImageInterface) {
                // render image
                try {
                    $result = $dummyImage->get($f);
                } catch (\RuntimeException $e) {
                    // Render image as png if get() method fails
                    $result = $dummyImage->get($this->settings['dummyImage']['fallbackFormat']);
                }
                if (!$result) {
                    throw new \RuntimeException('Something went wrong without throwing an exception');
                }

                // build result
                /** @phpstan-ignore-next-line */
                if (method_exists($this->response, 'setHttpHeader')) {
                    $this->response->setHttpHeader('Cache-Control', 'max-age=883000000');
                } elseif (method_exists($this->response, 'setComponentParameter') && class_exists('\Neos\Flow\Http\Component\SetHeaderComponent')) {
                    $this->response->setComponentParameter(\Neos\Flow\Http\Component\SetHeaderComponent::class, 'Cache-Control', 'max-age=883000000');
                }
                $this->response->setContentType('image/' . $f);

                return $result;
            } else {
                $this->response->setStatusCode(500);
                $this->response->setContentType('image/png');

                return file_get_contents('resource://Sitegeist.Kaleidoscope/Public/Images/imageError.png') ?: '';
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage(), LogEnvironment::fromMethodName(__METHOD__));

            // something went wrong we return the error image png
            $this->response->setStatusCode(500);
            $this->response->setContentType('image/png');

            return file_get_contents('resource://Sitegeist.Kaleidoscope/Public/Images/imageError.png') ?: '';
        }
    }
}
