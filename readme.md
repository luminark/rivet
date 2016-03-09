# Luminark Attachment Package

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

[![Code Coverage][ico-codecov]][link-codecov]
[![SensioLabsInsight][ico-sensio]][link-sension]

Attachment package provides foundation for easy and rapid development of sortable attachment Eloquent models for your app. Models representing file attachments, gallery images, or similar can be easily added to your app and attached to existing models. 

## Install

Via Composer

``` bash
$ composer require luminark/attachment
```

## Usage

Eloquent models that have one or many attachment models must use the `HasAttachmentsTrait` trait. This trait takes advantage of PHP's magic methods to allow quick use without much coding, while leaving room for further optimization.

For example, lets assume we have a model `Page` and we want it to have a collection of `FileAttachment`s and a single `CoverImage`.

``` php
class Page extends Model
{
    use HasAttachmentsTrait;
    
    protected function getAttachableConfig()
    {
        return [
            'fileAttachments' => ['collection', FileAttachment::class],
            'coverImage' => ['property', CoverImage::class]
        ];
    }
}

```

We use the `getAttachableConfig` method to define properties on the parent model that map to a collection, or a property of attachment models. Attachment models inherit from the base `Luminark\Attachment\Models\Attachment` class.

``` php
class CoverImage extends Attachment
{
    use UsesAttachmentsTableTrait;
    
    protected function getSerializableAttributes()
    {
        return ['file', 'alt', 'title'];
    }
}
```

Attachment classes can share the base `attachments` table. Although this doesn't translate to a normalized database, it's a variant of the single table inheritance object mapping that allows us to develop and setup a working app very quickly. If you want the extending attachment object to share the base table, simply have it use the `Luminark\Attachment\Traits\UsesAttachmentsTableTrait` trait.

Attachment classes have an attribute named `values`. The contents of this attribute are serialized when being stored to the database, and deserialized when reading from it, which makes for a convenient storage of variable amount of data. To take advantage of this, you can use the `getSerializableAttributes` method on the attachment model to define which elements of the `values` array can be accessed as model's attributes. With the class from example above, the following would work as expected.

``` php
$page = Page::find(1);
$coverImage = $page->coverImage;

$coverImage->title; // returns value of $coverImage->values['title']
```

Models implementing the `HasAttachmentsTrait` trait have several methods for setting and removing attachments at disposal out of the box.

``` php
$page = Page::find(1);

// setModelName and unsetModelName for model as property
$page->setCoverImage($dataArray);
$page->unsetCoverImage($modelIdOrModelObject);

// addModelName or removeModelName for models in collection
$page->addFileAttachment($dataArray);
$page->removeFileAttachment($dataArray);

// Getters
$page->coverImage;
$page->fileAttachments;
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mvrkljan@gmail.com instead of using the issue tracker.

## Credits

- [Martin Vrkljan][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/luminark/attachment.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/luminark/attachment/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/luminark/attachment.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/luminark/attachment.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/luminark/attachment.svg?style=flat-square
[ico-codecov]: https://img.shields.io/codecov/c/github/luminark/attachment.svg?style=flat-square
[ico-sension]: https://img.shields.io/sensiolabs/i/50e08a2b-75f2-4f5f-b3be-ab4b0d6c9110.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/luminark/rivet
[link-travis]: https://travis-ci.org/luminark/rivet
[link-scrutinizer]: https://scrutinizer-ci.com/g/luminark/rivet/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/luminark/rivet
[link-downloads]: https://packagist.org/packages/luminark/rivet
[link-author]: https://github.com/mvrkljan
[link-contributors]: ../../contributors
[link-codecov]: https://codecov.io/github/luminark/rivet
[link-sensio]: https://insight.sensiolabs.com/projects/04c0f40e-3cf6-4baf-8ba9-63e0a3d76b3a