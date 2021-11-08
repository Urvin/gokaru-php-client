<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Tests;

use Urvin\Gokaru\Cast;
use Urvin\Gokaru\Exception\InvalidArgumentException;
use Urvin\Gokaru\Signature\MurMurGenerator;
use Urvin\Gokaru\ThumbnailUrlBuilder;
use PHPUnit\Framework\TestCase;

class ThumbnailUrlBuilderTest extends TestCase
{
    private const DEFAULT_SALT = 'salt';
    private const DEFAULT_URL = 'http://gokaru.local/image';
    private const DEFAULT_SOURCE_TYPE = 'image';

    protected function getDefaultBuilderInstance(): ThumbnailUrlBuilder
    {
        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        return new ThumbnailUrlBuilder(self::DEFAULT_URL, self::DEFAULT_SOURCE_TYPE, $generator);
    }

    protected function getFilledBuilderInstance(): ThumbnailUrlBuilder
    {
        $builder = $this->getDefaultBuilderInstance();
        $builder
            ->category('category')
            ->filename('picture')
            ->extension('jpg')
            ->width(100)
            ->height(200)
            ->cast(Cast::RESIZE_INVERSE);
        return $builder;
    }

    public function test__construct()
    {
        $builder = $this->getDefaultBuilderInstance();
        $this->assertInstanceOf(ThumbnailUrlBuilder::class, $builder);
    }

    public function test__constructFailsWithUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        $builder = new ThumbnailUrlBuilder('', self::DEFAULT_SOURCE_TYPE, $generator);
    }

    public function test__constructFailsWithSourceType()
    {
        $this->expectException(InvalidArgumentException::class);
        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        $builder = new ThumbnailUrlBuilder(self::DEFAULT_URL, '', $generator);
    }

    public function test__toString()
    {
        $builder = $this->getFilledBuilderInstance();
        $this->assertEquals(
            'http://gokaru.local/image/18tom5f/category/100/200/8/picture.jpg',
            (string)$builder
        );
    }

    public function test__toStringFailsWithCategory()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getDefaultBuilderInstance();
        $builder
            ->filename('picture')
            ->extension('jpg')
            ->width(100)
            ->height(200)
            ->cast(Cast::RESIZE_INVERSE);


        $url = (string)$builder;
    }

    public function test__toStringFailsWithFilename()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getDefaultBuilderInstance();
        $builder
            ->category('category')
            ->extension('jpg')
            ->width(100)
            ->height(200)
            ->cast(Cast::RESIZE_INVERSE);


        $url = (string)$builder;
    }

    public function test__toStringFailsWithExtension()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getDefaultBuilderInstance();
        $builder
            ->category('category')
            ->filename('picture')
            ->width(100)
            ->height(200)
            ->cast(Cast::RESIZE_INVERSE);


        $url = (string)$builder;
    }

    public function testCategory()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->category('another_category');
        $this->assertEquals(
            'http://gokaru.local/image/149gbtf/another_category/100/200/8/picture.jpg',
            (string)$builder
        );
    }

    public function testCategoryFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->category('');
    }

    public function testFilename()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->filename('another_filename');
        $this->assertEquals(
            'http://gokaru.local/image/2dp9g0d/category/100/200/8/another_filename.jpg',
            (string)$builder
        );
    }

    public function testFilenameFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->filename('');
    }

    public function testExtension()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->extension('webp');
        $this->assertEquals(
            'http://gokaru.local/image/30s3bt3/category/100/200/8/picture.webp',
            (string)$builder
        );
    }

    public function testExtensionFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->extension('');
    }

    public function testCast()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->cast(Cast::OPAQUE_BACKGROUND);
        $this->assertEquals(
            'http://gokaru.local/image/10hn4n0/category/100/200/72/picture.jpg',
            (string)$builder
        );
    }

    public function testCastNil()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->cast(0);
        $this->assertEquals(
            'http://gokaru.local/image/31skno1/category/100/200/0/picture.jpg',
            (string)$builder
        );
    }

    public function testCastFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->cast(-7);
    }

    public function testWidth()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->width(845);
        $this->assertEquals(
            'http://gokaru.local/image/15ikl8u/category/845/200/8/picture.jpg',
            (string)$builder
        );
    }

    public function testWidthFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->width(-2);
    }

    public function testHeight()
    {
        $builder = $this->getFilledBuilderInstance();
        $builder->height(462);
        $this->assertEquals(
            'http://gokaru.local/image/ha2dt6/category/100/462/8/picture.jpg',
            (string)$builder
        );
    }

    public function testHeightFails()
    {
        $this->expectException(InvalidArgumentException::class);

        $builder = $this->getFilledBuilderInstance();
        $builder->height(-2);
    }

}
