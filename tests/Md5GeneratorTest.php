<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Tests;

use Urvin\Gokaru\Signature\Generator;
use Urvin\Gokaru\Signature\Md5Generator;
use PHPUnit\Framework\TestCase;

class Md5GeneratorTest extends TestCase
{

    public function test__construct()
    {
        $generator = new Md5Generator('salt');
        $this->assertInstanceOf(Md5Generator::class, $generator);
        $this->assertInstanceOf(Generator::class, $generator);
    }

    public function testSign()
    {
        $generator = new Md5Generator('salt');
        $hash = $generator->Sign('image', 'main', 'picture.jpg', 100, 200, 8);
        $this->assertEquals('85f854b2f1f819eb2c7b7e694254d40a', $hash);
    }
}
