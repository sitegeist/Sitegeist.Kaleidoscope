<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Tests\Unit\Domain;

use Sitegeist\Kaleidoscope\Domain\DummyImageSource;
use Sitegeist\Kaleidoscope\Tests\Unit\BaseTestCase;

class AbstractScalableImageSourceTest extends BaseTestCase
{
    protected $logger = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
    }

    /**
     * @test
     */
    public function aspectRatioIsHonored()
    {
        $dummy = $this->getDummyImageSource(400, 400);
        $copy = $dummy->withWidth(200, true);
        $this->assertEquals(200, $copy->height());
    }

    /**
     * @test
     */
    public function srcsetIsGenerated()
    {
        $dummy = $this->getDummyImageSource(400, 400);
        $this->assertEquals(
            'https://example.com?w=200&h=200&bg=999&fg=fff&t=Test 200w, https://example.com?w=400&h=400&bg=999&fg=fff&t=Test 400w',
            $dummy->srcset('200w, 400w')
        );
    }

    /**
     * @test
     */
    public function srcsetWithWidthAdheresToDefinition()
    {
        $dummy = $this->getDummyImageSource(400, 400);
        $this->assertEquals(
            'https://example.com?w=200&h=200&bg=999&fg=fff&t=Test 200w, https://example.com?w=400&h=400&bg=999&fg=fff&t=Test 400w, https://example.com?w=600&h=600&bg=999&fg=fff&t=Test 600w',
            $dummy->srcset('200w, 400w, 600w', allowUpScaling: true)
        );
    }

    /**
     * If the actual image is smaller than the requested size, then the image should be returned in its original size.
     * @test
     */
    public function srcsetWithWidthShouldOutputOnlyAvailableSources()
    {
        $dummy = $this->getDummyImageSource(500, 500);
        $this->assertEquals(
            'https://example.com?w=200&h=200&bg=999&fg=fff&t=Test 200w, https://example.com?w=400&h=400&bg=999&fg=fff&t=Test 400w, https://example.com?w=500&h=500&bg=999&fg=fff&t=Test 500w',
            $dummy->srcset('200w, 400w, 600w')
        );
    }

    /**
     * @test
     */
    public function srcsetWithRatioAdheresToDefinition()
    {
        $dummy = $this->getDummyImageSource(400, 200);
        $copy = $dummy->withHeight(50, true);
        $this->assertEquals(
            'https://example.com?w=100&h=50&bg=999&fg=fff&t=Test 1x, https://example.com?w=200&h=100&bg=999&fg=fff&t=Test 2x, https://example.com?w=300&h=150&bg=999&fg=fff&t=Test 3x',
            $copy->srcset('1x, 2x, 3x')
        );
    }

    /**
     * If the actual image is smaller than the requested size, then the image should be returned in its original size.
     * @test
     */
    public function srcsetWithRatioShouldOutputOnlyAvailableSources()
    {
        $dummy = $this->getDummyImageSource(30, 12);
        $copy = $dummy->withWidth(20, true);
        $this->assertEquals(
            'https://example.com?w=20&h=8&bg=999&fg=fff&t=Test 1x, https://example.com?w=30&h=12&bg=999&fg=fff&t=Test 1.5x',
            $copy->srcset('1x, 2x')
        );
    }

    /**
     * Log a warning if the descriptors are mixed between width and factor
     * @test
     */
    public function srcsetShouldWarnIfMixedDescriptors()
    {
        $dummy = $this->getDummyImageSource(650, 320);
        $this->logger->expects($this->once())->method('warning')->with($this->equalTo('Mixed media descriptors are not valid: [1x, 100w]'));

        $dummy->srcset('1x, 100w');
    }

    /**
     * Skip srcset descriptor if it does not match the first matched descriptor
     * @test
     */
    public function srcsetShouldSkipMixedDescriptors()
    {
        $dummy = $this->getDummyImageSource(500, 300);
        $this->assertEquals(
            'https://example.com?w=200&h=120&bg=999&fg=fff&t=Test 200w, https://example.com?w=440&h=264&bg=999&fg=fff&t=Test 440w',
            $dummy->srcset('200w, 1x, 440w')
        );
    }

    /**
     * Log a warning if the descriptor is invalid
     * @test
     */
    public function srcsetShouldWarnIfMissingDescriptor()
    {
        $dummy = $this->getDummyImageSource(30, 12);
        $this->logger->expects($this->once())->method('warning')->with($this->equalTo('Invalid media descriptor "1a". Missing type "x" or "w"'));

        $dummy->srcset('1a, 10w');
    }

    /**
     * Should skip srcset descriptor if either width w or factor x is missing
     * @test
     */
    public function srcsetShouldSkipMissingDescriptors()
    {
        $dummy = $this->getDummyImageSource(200, 400);
        $this->assertEquals(
            'https://example.com?w=100&h=200&bg=999&fg=fff&t=Test 100w, https://example.com?w=200&h=400&bg=999&fg=fff&t=Test 200w',
            $dummy->srcset('100w, 150, 200w')
        );
    }

    protected function getDummyImageSource($width, $height)
    {
        $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', $width, $height, '999', 'fff', 'Test');
        $this->inject($dummy, 'logger', $this->logger);
        return $dummy;
    }
}