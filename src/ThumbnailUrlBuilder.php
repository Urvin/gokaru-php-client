<?php

declare(strict_types=1);

namespace Urvin\Gokaru;

use Urvin\Gokaru\Exception\InvalidArgumentException;
use Urvin\Gokaru\Signature\Generator;

class ThumbnailUrlBuilder
{
    /** @var string */
    protected string $urlPublic;
    /** @var Generator */
    protected Generator $signature;
    /** @var string */
    protected string $sourceType;

    /** @var int */
    protected int $width = 0;
    /** @var int */
    protected int $height = 0;
    /** @var int */
    protected int $cast = 0;
    /** @var string */
    protected string $category = '';
    /** @var string */
    protected string $filename = '';
    /** @var string */
    protected string $extension = '';

    /**
     * ThumbnailUrlBuilder constructor.
     * @param string $urlPublic
     * @param string $sourceType
     * @param Generator $signature
     */
    public function __construct(string $urlPublic, string $sourceType, Generator $signature)
    {
        if (empty($urlPublic)) {
            throw new Exception\InvalidArgumentException('Public url should not be empty');
        }
        if (empty($sourceType)) {
            throw new Exception\InvalidArgumentException('Source type should not be empty');
        }
        $this->urlPublic = rtrim($urlPublic, '/');
        $this->sourceType = $sourceType;
        $this->signature = $signature;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function filename(string $filename): self
    {
        $this->validateFilename($filename);
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function extension(string $extension): self
    {
        $this->validateExtension($extension);
        $this->extension = $extension;
        return $this;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function category(string $category): self
    {
        $this->validateCategory($category);
        $this->category = $category;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function width(int $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Width should be positive');
        }
        $this->width = $value;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function height(int $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Height should be positive');
        }
        $this->height = $value;
        return $this;
    }

    /**
     * @param int $cast
     * @return $this
     */
    public function cast(int $cast): self
    {
        if (empty($cast)) {
            $this->cast = 0;
        } elseif ($cast < 0) {
            throw new InvalidArgumentException('Cast flag should be positive');
        } else {
            $this->cast |= $cast;
        }

        return $this;
    }

    /**
     * @param string $filename
     */
    protected function validateFilename(string $filename): void
    {
        if (empty($filename)) {
            throw new InvalidArgumentException('Filename should not be empty');
        }
    }

    /**
     * @param string $category
     */
    protected function validateCategory(string $category): void
    {
        if (empty($category)) {
            throw new InvalidArgumentException('Category should not be empty');
        }
    }

    /**
     * @param string $extension
     */
    protected function validateExtension(string $extension): void
    {
        if (empty($extension)) {
            throw new InvalidArgumentException('Extension should not be empty');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $this->validateCategory($this->category);
        $this->validateFilename($this->filename);
        $this->validateExtension($this->extension);

        $hash = $this->signature->Sign(
            $this->sourceType,
            $this->category,
            $this->filename . '.' . $this->extension,
            $this->width,
            $this->height,
            $this->cast
        );

        return $this->urlPublic . '/' . implode(
                '/',
                array_map(
                    'urlencode',
                    [
                        $hash,
                        $this->category,
                        $this->width,
                        $this->height,
                        $this->cast,
                        $this->filename . '.' . $this->extension
                    ]
                )
            );
    }

}