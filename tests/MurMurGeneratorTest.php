<?php

declare(strict_types=1);

namespace Urvin\Gokaru\Tests;

use Urvin\Gokaru\Signature\Generator;
use Urvin\Gokaru\Signature\MurMurGenerator;
use PHPUnit\Framework\TestCase;

class MurMurGeneratorTest extends TestCase
{

    public function test__construct()
    {
        $generator = new MurMurGenerator('salt');
        $this->assertInstanceOf(MurMurGenerator::class, $generator);
        $this->assertInstanceOf(Generator::class, $generator);
    }

    public function testSign()
    {
        $generator = new MurMurGenerator('salt');
        $hash = $generator->Sign('image', 'main', 'picture.jpg', 100, 200, 8);
        $this->assertEquals('vukrbi', $hash);
    }
}
