# Sitegeist.Kaleidoscope

## Responsive Images for Neos

This package implements responsive-images for Neos.

By separating the aspects of image-definition, size-constraining and  rendering
we enable the separation of those aspects into different fusion-components.

We want to help implementing responsive-images in the context of atomic-fusion
and enable previewing fusion-components and their full responsive behavior in the
Sitegeist.Monocle living styleguide.

Sitegeist.Kaleidoscope comes with four Fusion-ImageSources:

- Assets: Images uploaded by Editors
- DummyImages: Dummyimages created by a local service
- Resources: Static resources from Packages
- static Uris: any Url

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de
* Wilhelm Behncke - behncke@sitegeist.de

*The development and the public-releases of this package is generously sponsored
by our employer http://www.sitegeist.de.*

## Installation

Sitegeist.Kaleidoscope is available via packagist run `composer require sitegeist/kaleidoscope`.
We use semantic-versioning so every breaking change will increase the major-version number.

## Usage

## Image/Picture FusionObjects

The Kaleidoscope package integrates two main fusion-objects that an render
the given ImageSource as `img`- or `picture`-tag.

### `Sitegeist.Kaleidoscope:Image`

Render an `img`-tag with optional `srcset` based on `sizes` or `resolutions`.

Props:

- `imageSource`: the imageSource to render
- `sizes`: array of the needed media queries for the sizes attribute (if no width is given the keys are used as widths)
- `widths`: array of image-widths that shall be rendered (if no `sizes` but are given `sizes="100vw"` is assumed)
- `resolutions`: an array of numbers that represents the needed resolutions
- `alt`: alt-attribute for the tag
- `title`: title attribute for the tag


#### Image with srcset in multiple resolutions:

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource
resolutions = ${[1,2,3]}

renderer = afx`
    <Sitegeist.Kaleidoscope:Image imageSource={props.imageSource} resolutions={props.resolutions} />
`
```
will render as:

```
<img src="_baseurl_" srcset="_url_1_ 1x, _url_2_ 2x, _url_3_ 3x">
```

#### Image with srcset in multiple sizes:

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource
widths = ${[320, 400, 600, 800, 1000, 1200, 1600]}
sizes = Neos.Fusion:RawArray {
    large = '(min-width: 800px) 1000px'
    medium = '(min-width: 480px) 800px'
    small = '(min-width: 320px) 440px'
    default = '100vw'
}

renderer = afx`
    <Sitegeist.Kaleidoscope:Image imageSource={props.imageSource} widths={props.widths} sizes={props.sizes} />
`
```

will render as:

```
<img
    src="_baseurl_"
    srcset="_url_1_ 320w, _url_2_ 400w, _url_3_ 600w, _url_4_ 800w, _url_5_ 1000w, _url_6_ 1200w, _url_7_ 1600w"
    sizes="(min-width: 800px) 1000px, (min-width: 480px) 800px, (min-width: 320px) 440px, 100vw"
/>
```

If the sizes map 1:1 to the different widths the the syntax can be simplified by
using the keys of sizes as width definitions.

Attention: The keys are sorted in this case so make sure to define the smaller breakpoints at start.

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource
sizes = Neos.Fusion:RawArray {
    280 = '(max-width: 280px) 280px'
    440 = '(max-width: 480px) 440px'
    800 = '(max-width: 800px) 800px'
    1000 = '100vw'
}

renderer = afx`
    <Sitegeist.Kaleidoscope:Image imageSource={props.imageSource} sizes={props.sizes} />
`
```

Note: `widths` and / or `sizes are preferred to `resolutions`. If neither is passed the whole `srcset is omitted and only the `src`-attribute is rendered.

### `Sitegeist.Kaleidoscope:Picture`
`
Render a `picture`-tag with various sources.

Props:
- `imageSource`: the imageSource to render
- `sources`: an array of source definitions that contains. Each item contains the keys `media`, `sizes`, `widths` or `resolutions` and an optional `imageSource`
- `sizes`: array of the needed media queries for the sizes attribute of the default image
- `widths`: array of image-widths that shall be rendered for the default image
- `resolutions`: an array of numbers that represents the needed resolutions for the default image
- `alt`: alt-attribute for the tag
- `title`: title attribute for the tag

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource
sources = Neos.Fusion:RawArray {
    large = Neos.Fusion:RawArray {
        resolutions = ${[1, 1.5, 2]}
        media = 'screen and (min-width: 1600px)'
    }

    small = Neos.Fusion:RawArray {
        widths = ${[320,480,800]}
        sizes = Neos.Fusion:RawArray {
            small = '(max-width: 320px) 280px'
            medium = '(max-width: 480px) 440px'
            large = '100vw'
        }
        media = 'screen and (max-width: 1599px)'
    }

    print = Neos.Fusion:RawArray {
        imageSource = Sitegeist.Kaleidoscope:DummyImageSource {
            text = "im am here for printing"
        }
        media = 'print'
    }
}

renderer = afx`
    <Sitegeist.Kaleidoscope:Picture imageSource={props.imageSource} sources={props.sources} />
`
```

will render as:

```
<picture>
  <source
    srcset="_large_url_1_ 1x, _large_url_2_ 1.5x, _large_url_3_ 2x"
    media="screen and (min-width: 1600px)"
    />
  <source
    srcset="_small_url_1_ 320w, _small_url_2_ 480w, _small_url_3_ 800w, _small_url_4_ 1000w"
    sizes="(max-width: 320px) 280px, (max-width: 480px) 440px, 800px"
    media="screen and (max-width: 1599px)"
    />
  <source
    srcset="_print_url_1_"
    media="print"
    />
  <img src="_base_url_">
</picture>
```

## Responsive Images with AtomicFusion-Components and Sitegeist.Monocle

```
prototype (Vendor.Site:Component.ResponsiveKevisualImage) < prototype(Neos.Fusion:Component) {

    #
    # Use the DummyImageSource inside the styleguide
    #
    @styleguide {
        props {
            imageSource = Sitegeist.Kaleidoscope:DummyImageSource
        }
    }

    #
    # Enforce the dimensions of the passed images by cropping to 1600 x 800
    #
    imageSource = null
    imageSource.@process.enforeDimensions = ${value.setWidth(1600).setHeight(900)}

    renderer = afx`
        <img class="keyvisual" src={props.imageSource} srcset={props.imageSource.resolutionSrcset([1,1.5,2])} />
    `
}
```

Please note that the enforced dimensions are applied in the presentational component.
The dimension enforcement is applied to the DummySource aswell as to the AssetSource
which will be defined by the integration.

The integration of the component above as content-element works like this:

```
prototype (Vendor.Site:Content.ResponsiveKevisual) < prototype(Neos.Neos:ContentComponent) {
    renderer = Vendor.Site:Component.ResponsiveKevisualImage {
        imageSource = Sitegeist.Kaleidoscope:AssetImageSource {
            asset = ${q(node).property('image')}
        }
    }
}
```

This shows that integration-code dos not need to know the required image dimensions or wich
variants are needed. This frontend know-how is now encapsulated into the presentational-component.

## ImageSource FusionObjects

The package contains ImageSource-FusionObjects that encapsulate the intention to
render an image. ImageSource-Objects return Eel-Helpers that allow to
enforcing the rendered dimensions later in the rendering process.

Note: The settings for `width`, `height` and `preset can be defined via fusion
but can also applied on the returned object. This will override the fusion-settings.

All ImageSources support the following fusion properties:

- `preset`: Set width and/or height via named-preset from Settings `Neos.Media.thumbnailPresets` (default null, settings below override the preset)
- `width`: Set the intended width (default null)
- `height`: Set the intended height (default null)

### `Sitegeist.Kaleidoscope:AssetImageSource`

Arguments:

- `asset`: An image asset that shall be rendered (defaults to the context value `asset`)
- `async`: Defer image-rendering until the image is actually requested by the browser
- `preset`, `width` and `height` are supported as explained above

### `Sitegeist.Kaleidoscope:DummyImageSource`

Arguments:
- `baseWidth`: The default width for the image before scaling (default = 600)
- `baseHeight`: The default height for the image before scaling (default = 400)
- `backgroundColor`: The background color of the dummyimage (default = '999')
- `foregroundColor`: The foreground color of the dummyimage (default = 'fff')
- `text`: The text that is rendered on the image (default = null, show size)
- `preset`, `width` and `height` are supported as explained above


### `Sitegeist.Kaleidoscope:UriImageSource`

Arguments:
- `uri`: The uri that will be rendered
- !!! `preset`, `width` and `height` have no effect on this ImageSource

### `Sitegeist.Kaleidoscope:ResourceImageSource`

Arguments:
- `package`: The default width for the image before scaling (default = 600)
- `path`: The default height for the image before scaling (default = 400)
- !!! `preset`, `width` and `height` have no effect on this ImageSource

## ImageSource EEl-Helpers

The ImageSource-helpers are created by the fusion-objects above and are passed to a
rendering component. The helpers allow to set or override the intended
dimensions and to render the `src` and `srcset`-attributes.

Methods of ImageSource-Helpers that are accessible via EEL:

- `applyPreset( string )`: Set width and/or height via named-preset from Settings `Neos.Media.thumbnailPresets`
- `setWidth( integer )`: Set the intend width
- `setHeight( integer )`: Set the intended height
- `src ()` : Render a src attribute for the given ImageSource-object
- `widthSrcset ( array of integers )` : render a srcset attribute for the ImageSource with width descriptors.
- `resolutionSrcset ( array of floats )` : render a srcset attribute for the ImageSource with pixel density descriptors.

Note: The Eel-helpers cannot be created directly. They have to be created
by using the `Sitegeist.Kaleidoscope:AssetImageSource` or
`Sitegeist.Kaleidoscope:DummyImageSource` fusion-objects.

### Examples

Render an `img`-tag with `src` and a `srcset` in multiple resolutions:

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <img
            src={props.imageSource}
            srcset={props.imageSource.resolutionSrcset([1,1.5,2])}
        />
    `
```

Render an `img`-tag with `src` plus `srcset` and `sizes`:

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <img
            src={props.imageSource}
            srcset={props.imageSource.widthSrcset([400,600,800])}
            sizes="(max-width: 320px) 280px, (max-width: 480px) 440px, 800px"
        />
    `
```
Render a `picture`-tag with multiple `source`-children and an `img`-fallback :

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <picture>
            <source srcset={props.imageSource.width(400).height(400)} media="(max-width: 799px)" />
            <source srcset={props.imageSource.resolutionSrcset([1,1.5,2])} media="(min-width: 800px)" />
            <img src={props.imageSource} />
        </picture>
    `
```

In this example devices smaller than 800px will show a 400x400 square image,
while larger devices will render a multires-source in the orginal image dimension.



## Contribution

We will gladly accept contributions. Please send us pull requests.
