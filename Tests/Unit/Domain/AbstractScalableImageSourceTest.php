<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Tests\Unit\Domain;

use Sitegeist\Kaleidoscope\Domain\DummyImageSource;
use Sitegeist\Kaleidoscope\Tests\Unit\BaseTestCase;

class AbstractScalableImageSourceTest extends BaseTestCase
{
    /**
     * @test
     */
    public function aspectRatioIsHonored()
    {
        $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 400, 400, '999', 'fff', 'Test');
        $copy = $dummy->withWidth(200, true);
        $this->assertEquals(200, $copy->height());
    }

    /**
     * @test
     */
    public function srcsetIsGenerated()
    {
        $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 400, 400, '999', 'fff', 'Test');
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
        $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 400, 400, '999', 'fff', 'Test');
        $this->assertEquals(
            'https://example.com?w=200&h=200&bg=999&fg=fff&t=Test 200w, https://example.com?w=400&h=400&bg=999&fg=fff&t=Test 400w, https://example.com?w=600&h=600&bg=999&fg=fff&t=Test 600w',
            $dummy->srcset('200w, 400w, 600w')
        );
    }

    /**
     * If the actual image is smaller than the requested size, then the image should be returned in its original size.
     * @test
     * @todo
     */
    #public function srcsetWithWidthShouldOutputOnlyAvailableSources()
    #{
    #    $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 500, 500, '999', 'fff', 'Test');
    #    $this->assertEquals(
    #        'https://example.com?w=200&h=200&bg=999&fg=fff&t=Test 200w, https://example.com?w=400&h=400&bg=999&fg=fff&t=Test 400w, https://example.com?w=500&h=500&bg=999&fg=fff&t=Test 500w',
    #        $dummy->srcset('200w, 400w, 600w')
    #    );
    #}

    /**
     * @test
     */
    public function srcsetWithRatioAdheresToDefinition()
    {
        $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 400, 200, '999', 'fff', 'Test');
        $copy = $dummy->withHeight(50, true);
        $this->assertEquals(
            'https://example.com?w=100&h=50&bg=999&fg=fff&t=Test 1x, https://example.com?w=200&h=100&bg=999&fg=fff&t=Test 2x, https://example.com?w=300&h=150&bg=999&fg=fff&t=Test 3x',
            $copy->srcset('1x, 2x, 3x')
        );
    }

    /**
     * If the actual image is smaller than the requested size, then the image should be returned in its original size.
     * @test
     * @todo
     */
    #public function srcsetWithRatioShouldOutputOnlyAvailableSources()
    #{
    #    $dummy = new DummyImageSource('https://example.com', 'Test', 'Test', 30, 12, '999', 'fff', 'Test');
    #    $copy = $dummy->withWidth(20, true);
    #    $this->assertEquals(
    #        'https://example.com?w=20&h=8&bg=999&fg=fff&t=Test 1x, https://example.com?w=30&h=12&bg=999&fg=fff&t=Test 1.5x',
    #        $copy->srcset('1x, 2x')
    #    );
    #}
}