<?php

declare(strict_types=1);

namespace Urvin\Gokaru;

interface SourceType
{
    /** @var string */
    public const SOURCE_TYPE_FILE = 'file';
    /** @var string */
    public const SOURCE_TYPE_IMAGE = 'image';

    /** @var string[] */
    public const SOURCE_TYPES = [
        self::SOURCE_TYPE_FILE,
        self::SOURCE_TYPE_IMAGE
    ];
}