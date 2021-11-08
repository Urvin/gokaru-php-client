<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Urvin\Gokaru\Cast;
use Urvin\Gokaru\Client;
use PHPUnit\Framework\TestCase;
use Urvin\Gokaru\Exception\DomainException;
use Urvin\Gokaru\Exception\InvalidArgumentException;
use Urvin\Gokaru\Exception\RuntimeException;
use Urvin\Gokaru\Signature\Generator;
use Urvin\Gokaru\Signature\Md5Generator;
use Urvin\Gokaru\Signature\MurMurGenerator;
use Urvin\Gokaru\SourceType;
use Urvin\Gokaru\Tests\MockFacades\HttpMockableClient;
use Urvin\Gokaru\ThumbnailUrlBuilder;

class ClientTest extends TestCase
{
    private const DEFAULT_SALT = 'salt';
    private const DEFAULT_URL = 'http://gokaru.local/';
    private const DEFAULT_URL_WO_SLASH = 'http://gokaru.local';
    private const DEFAULT_SOURCE_TYPE = 'image';


    protected function setUp(): void
    {
        $filename = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'file_unreadable.txt');
        @chmod($filename, 0220);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $filename = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'file_unreadable.txt');
        @chmod($filename, 0664);
        parent::tearDown();
    }

    protected function getDefaultClient(): Client
    {
        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        return new Client(self::DEFAULT_URL, $generator);
    }

    public function test__construct()
    {
        $client = $this->getDefaultClient();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals(self::DEFAULT_URL_WO_SLASH, $client->getUrl());
        $this->assertInstanceOf(Generator::class, $client->getSignature());
    }

    public function testSetUrl()
    {
        $client = $this->getDefaultClient();
        $client->setUrl(self::DEFAULT_URL_WO_SLASH);
        $this->assertEquals(self::DEFAULT_URL_WO_SLASH, $client->getUrl());
        $client->setUrl(self::DEFAULT_URL);
        $this->assertEquals(self::DEFAULT_URL_WO_SLASH, $client->getUrl());
    }

    public function testSetUrlFails()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = $this->getDefaultClient();
        $client->setUrl('');
    }

    public function testSetSignature()
    {
        $client = $this->getDefaultClient();
        $this->assertInstanceOf(Generator::class, $client->getSignature());
        $this->assertInstanceOf(MurMurGenerator::class, $client->getSignature());
        $client->setSignature((new Md5Generator(self::DEFAULT_SALT)));
        $this->assertInstanceOf(Generator::class, $client->getSignature());
        $this->assertInstanceOf(Md5Generator::class, $client->getSignature());
    }

    public function testSetUrlPublic()
    {
        $client = $this->getDefaultClient();
        $client->setUrlPublic(SourceType::SOURCE_TYPE_IMAGE, 'http://gokaru.public/storage/image/');
        $client->setUrlPublic(SourceType::SOURCE_TYPE_FILE, 'http://gokaru.public/storage/file/');

        $this->assertEquals('http://gokaru.public/storage/image', $client->getPublicUrl(SourceType::SOURCE_TYPE_IMAGE));
        $this->assertEquals('http://gokaru.public/storage/file', $client->getPublicUrl(SourceType::SOURCE_TYPE_FILE));

        $client->setUrlPublic(SourceType::SOURCE_TYPE_FILE, '');
        $this->assertEquals('http://gokaru.local/file', $client->getPublicUrl(SourceType::SOURCE_TYPE_FILE));
    }

    public function testSetUrlPublicFailsWithUnknownType()
    {
        $this->expectException(DomainException::class);
        $client = $this->getDefaultClient();
        $client->setUrlPublic('unknown_type', 'http://gokaru.public/storage/unknown_type/');
    }

    public function testSetUrlPublicFailsWithEmptyType()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = $this->getDefaultClient();
        $client->setUrlPublic('', 'http://gokaru.public/storage/empty_type/');
    }

    public function testThumbnail()
    {
        $client = $this->getDefaultClient();

        $builder = $client->thumbnail();
        $this->assertInstanceOf(ThumbnailUrlBuilder::class, $builder);

        $thumbnail = (string)($client->thumbnail(100, 200, Cast::RESIZE_INVERSE, 'category', 'picture', 'webp'));
        $this->assertEquals('http://gokaru.local/image/30s3bt3/category/100/200/8/picture.webp', $thumbnail);
    }

    public function testOrigin()
    {
        $client = $this->getDefaultClient();
        $origin = $client->origin(SourceType::SOURCE_TYPE_IMAGE, 'category', 'picture');
        $this->assertEquals('http://gokaru.local/image/category/picture', $origin);
    }

    public function testOriginFailsWithEmptyCategory()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = $this->getDefaultClient();
        $origin = $client->origin(SourceType::SOURCE_TYPE_IMAGE, '', 'picture');
    }

    public function testOriginFailsWithEmptyFilename()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = $this->getDefaultClient();
        $origin = $client->origin(SourceType::SOURCE_TYPE_IMAGE, 'category', '');
    }

    public function testFile()
    {
        $client = $this->getDefaultClient();
        $file = $client->file('documents', 'secret.pdf');
        $this->assertEquals('http://gokaru.local/file/documents/secret.pdf', $file);

        $client->setUrlPublic(SourceType::SOURCE_TYPE_FILE, 'http://gokaru.public/storage/file/');
        $file = $client->file('documents', 'secret.pdf');
        $this->assertEquals('http://gokaru.public/storage/file/documents/secret.pdf', $file);
    }


    public function testUpload()
    {
        $mock = new MockHandler([
            new Response(201, ['Content-Length' => 0]),
            new Response(500, ['Content-Length' => 0]),
        ]);
        $handler = HandlerStack::create($mock);

        $guzzle = new GuzzleClient([
            'handler' => $handler,
            'base_uri' => self::DEFAULT_URL
        ]);

        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        $client = new HttpMockableClient(self::DEFAULT_URL, $generator);
        $client->setHttpClient($guzzle);

        $filename = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'file.txt');

        $client->upload($filename, SourceType::SOURCE_TYPE_FILE, 'documents', 'secret.txt');

        $this->expectException(RuntimeException::class);
        $client->upload($filename, SourceType::SOURCE_TYPE_FILE, 'documents', 'secret.txt');
    }

    public function testUploadFailsWithEmptyFilename()
    {
        $this->expectException(InvalidArgumentException::class);
        $client = $this->getDefaultClient();
        $client->upload('', SourceType::SOURCE_TYPE_FILE, 'documents', 'secret.txt');
    }

    public function testUploadFailsWithoutAFile()
    {
        $this->expectException(RuntimeException::class);
        $client = $this->getDefaultClient();
        $client->upload('/any/file/that/doesnt/exist.fail', SourceType::SOURCE_TYPE_FILE, 'documents', 'secret.txt');
    }

    public function testUploadFailsWithUnreadableFile()
    {
        $this->expectException(RuntimeException::class);
        $client = $this->getDefaultClient();
        $filename = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'file_unreadable.txt');
        $client->upload($filename, SourceType::SOURCE_TYPE_FILE, 'documents', 'secret.txt');
    }

    public function testDelete()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0]),
            new Response(500, ['Content-Length' => 0]),
        ]);
        $handler = HandlerStack::create($mock);

        $guzzle = new GuzzleClient([
            'handler' => $handler,
            'base_uri' => self::DEFAULT_URL
        ]);

        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        $client = new HttpMockableClient(self::DEFAULT_URL, $generator);
        $client->setHttpClient($guzzle);

        $client->delete(SourceType::SOURCE_TYPE_IMAGE, 'category', 'file');

        $this->expectException(ServerException::class);
        $client->delete(SourceType::SOURCE_TYPE_IMAGE, 'category', 'file');
    }

    public function testHttpClient()
    {
        $generator = new MurMurGenerator(self::DEFAULT_SALT);
        $client = new HttpMockableClient(self::DEFAULT_URL, $generator);
        $this->assertInstanceOf(GuzzleClient::class, $client->getHttpClient());
    }
}
