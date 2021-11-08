<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Signature;

interface Generator
{
    /**
     * @param string $sourceType
     * @param string $category
     * @param string $fileName
     * @param int $width
     * @param int $height
     * @param int $cast
     * @return string
     */
    public function Sign(
        string $sourceType,
        string $category,
        string $fileName,
        int $width,
        int $height,
        int $cast
    ): string;
}
