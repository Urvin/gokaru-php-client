<?php

declare(strict_types=1);

namespace Urvin\Gokaru;

interface Cast
{
    /** @var int Stretch image directly into defined width and height ignoring aspect ratio */
    public const RESIZE_TENSILE = 2;

    /** @var int Keep aspect-ratio, use higher dimension */
    public const RESIZE_PRECISE = 4;

    /** @var int Keep aspect-ratio, use lower dimension */
    public const RESIZE_INVERSE = 8;

    /** @var int Remove any edges that are exactly the same color as the corner pixels */
    public const TRIM = 16;

    /** @var int Set output canvas exactly defined width and height after image resize */
    public const EXTENT = 32;

    /** @var int Set image white opaque background */
    public const OPAQUE_BACKGROUND = 64;

    /** @var int Create a transparent background for an image */
    public const TRANSPARENT_BACKGROUND = 128;

    /** @var int Adds padding around your trimmed */
    public const TRIM_PADDING = 256;
}