<?php

namespace Tests\Common;

use ClockIn\Common\Duration;
use PHPUnit\Framework\TestCase;

class DurationTest extends TestCase
{
    public function testEquals(): void
    {
        $a = new Duration(100);
        $b = new Duration(100);

        self::assertTrue($a->equals($b));
    }

    public function testLessThan(): void
    {
        $a = new Duration(99);
        $b = new Duration(100);

        self::assertTrue($a->lessThan($b));
    }

    public function testMoreThan(): void
    {
        $a = new Duration(101);
        $b = new Duration(100);

        self::assertTrue($a->moreThan($b));
    }
}
