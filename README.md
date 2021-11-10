# Gokaru php client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-coverage]][link-coverage]
[![Quality Score][ico-code-quality-scrutinizer]][link-code-quality-scrutinizer]


PHP API client for [Gokaru][gokaru] storage & image thumbnail server.

## Requirements

- PHP >= 7.4

## Usage

```php
// According to your Gokaru configuration, create a MurMur or Md5 signature generator
$generator = new \Urvin\Gokaru\Signature\MurMurGenerator('gokaru_secret_passphrase'); 

// Create a client instance with gokaru secured url
$gokaru = new \Urvin\Gokaru\Client('http://localhost:8101/', $generator);

// Upload your first image
$gokaru->upload(
    '~/my_first_image.png',
    \Urvin\Gokaru\SourceType::SOURCE_TYPE_IMAGE,
    'tryout',          // choose any category you want
    'first_image'      // choose any file code you want 
);

// Or a file
$gokaru->upload(
    '~/my_secret_data.pdf',
    \Urvin\Gokaru\SourceType::SOURCE_TYPE_FILE,
    'x-files',
    'secret_data.pdf' 
);

// You can specify different public urls for different file types
$gokaru->setUrlPublic(
    \Urvin\Gokaru\SourceType::SOURCE_TYPE_IMAGE,
    'https://site.com/pictures'
);
$gokaru->setUrlPublic(
    \Urvin\Gokaru\SourceType::SOURCE_TYPE_FILE,
    'https://site.com/documents'
);

// Create a thumbnail url for your image
$builder = $gokaru->thumbnail()
    ->width(200)
    ->height(200)
    ->cast(\Urvin\Gokaru\Cast::RESIZE_PRECISE)
    ->cast(\Urvin\Gokaru\Cast::TRIM)
    ->cast(\Urvin\Gokaru\Cast::EXTENT)
    ->category('tryout')
    ->filename('first_image')
    ->extension('jpg');
$url = (string)$builder;

// When a moment comes, delete source and all thumbnails from server
$gokaru->delete(
    \Urvin\Gokaru\SourceType::SOURCE_TYPE_IMAGE,
    'tryout',
    'first_image'
);
```

## Author

Yuriy Gorbachev <yuriy@gorbachev.rocks>

[gokaru]:<https://github.com/Urvin/gokaru>

[ico-version]: https://img.shields.io/badge/packagist-1.0.3-yellow.svg
[ico-coverage]: https://scrutinizer-ci.com/g/Urvin/gokaru-php-client/badges/coverage.png?b=master
[ico-code-quality-scrutinizer]: https://scrutinizer-ci.com/g/Urvin/gokaru-php-client/badges/quality-score.png?b=master
[ico-downloads]: https://img.shields.io/packagist/dt/urvin/gokaru-php-client.svg

[link-packagist]: https://packagist.org/packages/urvin/gokaru-php-client
[link-coverage]: https://scrutinizer-ci.com/g/Urvin/gokaru-php-client/?branch=master
[link-code-quality-scrutinizer]: https://scrutinizer-ci.com/g/Urvin/gokaru-php-client/?branch=master
[link-downloads]: https://packagist.org/packages/urvin/phikaru