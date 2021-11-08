<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Signature;

use lastguest\Murmur;

class MurMurGenerator extends AbstractGenerator
{
    /**
     * @inheritDoc
     */
    protected function hash(string $input): string
    {
        return Murmur::hash3($input);
    }
}