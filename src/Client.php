<?php

declare(strict_types=1);

namespace Urvin\Gokaru;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Urvin\Gokaru\Exception;
use Urvin\Gokaru\Signature\Generator;

class Client
{
    /** @var string */
    protected string $url;
    /** @var Generator */
    protected Generator $signature;
    /** @var array<string,string> */
    protected array $urlPublic;

    /** @var GuzzleClient|null */
    protected ?GuzzleClient $httpClient = null;

    /**
     * @param string $url
     * @param Generator $signature
     */
    public function __construct(string $url, Generator $signature)
    {
        $this->setUrl($url);
        $this->setSignature($signature);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $url = rtrim($url, '/');
        if (empty($url)) {
            throw new Exception\InvalidArgumentException('Url should not be empty');
        }
        $this->url = $url;
    }

    /**
     * @return Generator
     */
    public function getSignature(): Generator
    {
        return $this->signature;
    }

    /**
     * @param Generator $signature
     */
    public function setSignature(Generator $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @param string $type
     * @param string $url
     */
    public function setUrlPublic(string $type, string $url): void
    {
        $this->validateSourceType($type);
        $url = rtrim($url, '/');
        if (empty($url) && isset($this->urlPublic[$type])) {
            unset($this->urlPublic[$type]);
            return;
        }

        $this->urlPublic[$type] = $url;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getPublicUrl(string $type): string
    {
        $this->validateSourceType($type);
        if (!empty($this->urlPublic[$type])) {
            return $this->urlPublic[$type];
        }
        return $this->getUrl() . '/' . $type;
    }

    /**
     * Upload sourceFilename into Gokaru
     *
     * @param string $sourceFilename
     * @param string $type
     * @param string $category
     * @param string $filename
     * @throws GuzzleException
     */
    public function upload(string $sourceFilename, string $type, string $category, string $filename): void
    {
        if (empty($sourceFilename)) {
            throw new Exception\InvalidArgumentException('Source file name is empty');
        }
        $this->validateCredentials($type, $category, $filename);

        if (!file_exists($sourceFilename)) {
            throw new Exception\RuntimeException('Source file does not exist');
        }
        $fileHandler = @fopen($sourceFilename, 'rb');
        if ($fileHandler === false) {
            throw new Exception\RuntimeException('Source file is not readable');
        }
        try {
            $this->getHttpClient()->put(
                $this->createUrl($type, $category, $filename),
                [
                    'body' => $fileHandler
                ]
            );
        } catch (RequestException $e) {
            throw  new  Exception\RuntimeException('Upload exception: ' . $e->getMessage(), 0, $e);
        } finally {
            if (is_resource($fileHandler)) {
                fclose($fileHandler);
            }
        }
    }

    /**
     * Delete origin file and thumbnails from Gokaru
     *
     * @param string $type
     * @param string $category
     * @param string $filename
     * @throws GuzzleException
     */
    public function delete(string $type, string $category, string $filename): void
    {
        $this->validateCredentials($type, $category, $filename);
        $this->getHttpClient()->delete($this->createUrl($type, $category, $filename));
    }

    /**
     * Get service origin file URL
     *
     * @param string $type
     * @param string $category
     * @param string $filename
     * @return string
     */
    public function origin(string $type, string $category, string $filename): string
    {
        $this->validateCredentials($type, $category, $filename);
        return $this->createUrl($type, $category, $filename);
    }

    /**
     * Get public file url
     *
     * @param string $category
     * @param string $filename
     * @return string
     */
    public function file(string $category, string $filename): string
    {
        $type = SourceType::SOURCE_TYPE_FILE;
        $this->validateCredentials($type, $category, $filename);
        return $this->createUrl($type, $category, $filename, true);
    }

    /**
     * Create Thumbnail url builder castable to string
     *
     * @param int|null $width
     * @param int|null $height
     * @param int|null $cast
     * @param string|null $filename
     * @param string|null $extension
     * @return ThumbnailUrlBuilder
     */
    public function thumbnail(
        ?int $width = null,
        ?int $height = null,
        ?int $cast = null,
        ?string $category = null,
        ?string $filename = null,
        ?string $extension = null
    ): ThumbnailUrlBuilder {
        $builder = new ThumbnailUrlBuilder(
            $this->getPublicUrl(SourceType::SOURCE_TYPE_IMAGE),
            SourceType::SOURCE_TYPE_IMAGE,
            $this->signature
        );

        if ($width !== null) {
            $builder->width($width);
        }
        if ($height !== null) {
            $builder->height($height);
        }
        if ($cast !== null) {
            $builder->cast($cast);
        }
        if (!empty($filename)) {
            $builder->category($category);
        }
        if (!empty($filename)) {
            $builder->filename($filename);
        }
        if (!empty($extension)) {
            $builder->extension($extension);
        }

        return $builder;
    }

    /**
     * @param string $type
     * @param string $category
     * @param string $filename
     */
    protected function validateCredentials(string $type, string $category, string $filename): void
    {
        $this->validateSourceType($type);
        if (empty($category)) {
            throw new Exception\InvalidArgumentException("Storage category should not be empty");
        }
        if (empty($filename)) {
            throw new Exception\InvalidArgumentException("Storage file name should not be empty");
        }
    }

    /**
     * @param string $type
     */
    protected function validateSourceType(string $type): void
    {
        if (empty($type)) {
            throw new Exception\InvalidArgumentException('File type not specified');
        }
        if (!in_array($type, SourceType::SOURCE_TYPES)) {
            throw new Exception\DomainException('Wrong file type specified');
        }
    }

    /**
     * @return GuzzleClient
     */
    protected function getHttpClient(): GuzzleClient
    {
        if ($this->httpClient === null) {
            $this->httpClient = new GuzzleClient();
        }
        return $this->httpClient;
    }

    /**
     * @param string $type
     * @param string $category
     * @param string $filename
     * @param bool $public
     * @return string
     */
    protected function createUrl(string $type, string $category, string $filename, bool $public = false): string
    {
        $baseUrl = $public ? $this->getPublicUrl($type) : $this->getUrl();
        $path = [
            $category,
            $filename
        ];
        if (!$public) {
            array_unshift($path, $type);
        }

        return $baseUrl . '/' . implode('/', array_map('urlencode', $path));
    }
}