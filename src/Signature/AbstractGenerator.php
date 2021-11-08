<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Signature;

abstract class AbstractGenerator implements Generator
{
    /** @var string */
    protected string $salt;

    /**
     * @param string $salt
     */
    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    /**
     * @inheritDoc
     */
    public function Sign(
        string $sourceType,
        string $category,
        string $fileName,
        int $width,
        int $height,
        int $cast
    ): string {
        return $this->hash(
            implode('/', [
                $this->salt,
                $sourceType,
                $category,
                $fileName,
                (string)$width,
                (string)$height,
                (string)$cast
            ])
        );
    }

    /**
     * @param string $input
     * @return string
     */
    abstract protected function hash(string $input): string;
}