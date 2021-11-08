<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Signature;

class Md5Generator extends AbstractGenerator
{
    /**
     * @inheritDoc
     */
    protected function hash(string $input): string
    {
        return md5($input);
    }
}