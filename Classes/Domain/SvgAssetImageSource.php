<?php
declare(strict_types=1);

namespace Sitegeist\Kaleidoscope\Domain;

class SvgAssetImageSource extends AssetImageSource {

    /**
     * Srcsets make no sense for svg as those are scalable anyways
     *
     * @param $mediaDescriptors
     * @return string
     */
    public function srcset($mediaDescriptors): string
    {
        return '';
    }
}
