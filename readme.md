# Luminark Rivet Package

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

[![Code Coverage][ico-codecov]][link-codecov]
[![SensioLabsInsight][ico-sensio]][link-sension]

Rivet package provides foundation for easy and rapid development of library-like, attachable and sortable Eloquent models for your app. Models representing file attachments, images or similar can be easily added to your app and attached to existing models. 

## Install

Via Composer

``` bash
$ composer require luminark/rivet
```

## Usage

Eloquent models that have one or many rivet models must use the `HasRivetsTrait` trait. This trait takes advantage of PHP's magic methods to allow quick use without much coding, while leaving room for further optimization.

For example, lets assume we have a model `Page` and we want it to have a collection of `Attachment`s and a single `Image`.

``` php
class Page extends Model
{
    use HasRivetsTrait;
    
    protected function getRivetsConfig()
    {
        return [
            'attachments' => ['collection', Attachment::class],
            'image' => ['property', Image::class]
        ];
    }
}

```

We use the `getRivetsConfig` method to define attributes on the parent model which map to a collection or a property of attachable models. Attachable models inherit from the base `Luminark\Attachment\Models\Rivet` class.

``` php
class Image extends Rivet
{
    protected $fillable = ['title', 'size'];

    public static function getMorphToManyName()
    {
        return 'imageable';
    }
}
```

Attaching classes can share the base `rivets` table for quicker prototyping. Although this doesn't translate to a normalized database, it's a variant of the single table inheritance object mapping that allows us to develop and setup a working app very quickly. If you want the extending model class to share the base table, simply have it use the `Luminark\Attachment\Traits\UsesRivetsTableTrait` trait.

If the extending model class is using its own database table, make sure to override the `getMorphToManyName` method which is used to properly map attaching models to parent models via polymorphic many-to-many relationship.

The `Attachment` model from our example can share the base table:

``` php
class Attachment extends Rivet
{
    use UsesRivetsTableTrait;

    protected function getSerializableAttributes()
    {
        return ['file', 'title'];
    }
}
```

The base `Rivet` class implements the `Luminark\SerializableValues\Traits\HasSerializableValuesTrait` which gives it (and all extending classes) access to `values` attribute. The contents of this attribute are serialized when being stored to the database, and deserialized when reading from it, which makes for a convenient storage of variable amount of data. To take advantage of this, you can use the `getSerializableAttributes` method on the attaching model to define which elements of the `values` array can be accessed as model's attributes. If the extending class is not sharing the `rivets` table, a `values` column should be added to extending class' table if this functionality is needed.

With the class from example above, the following would work as expected.

``` php
$page = Page::find(1);
$image = $page->image;

$image->title; // returns value of $image->values['title']
```

Models implementing the `HasRivetsTrait` trait have several methods for setting and removing attachable models at disposal out of the box.

``` php
$page = Page::find(1);

// setModelName and unsetModelName for model as property
$page->setImage($model);
$page->unsetImage($modelIdOrModelObject);

// addModelName or removeModelName for models in collection
$page->addAttachment($model);
$page->removeAttachment($modelIdOrModelObject);

// Getters
$page->image;
$page->attachments;
```

### File processing
A convenient class for processing files related to attachable models comes with the package. It is acessible via `Luminark\Rivet\Facades\FileProcessor` facade or via dependency injection by type hinting `Luminark\Rivet\Interfaces\FileProcessorInterface`. It has a single method `processFile` which takes the attachable model object for which the file is being processed and reference to a file (a `string` path, `Symfony\Component\HttpFoundation\File\File` object or `Symfony\Component\HttpFoundation\File\UploadedFile` object). This method will attempt to store the file to a storage disk defined in Laravel config and fire events.

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