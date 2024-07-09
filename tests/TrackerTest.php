<?php

use PHPUnit\Framework\TestCase;
use Tracker\Tracker;

class TrackerTest extends TestCase
{
    public function testStart(): void
    {
        $tracker = new Tracker();

        $tracker->start('First task');

        self::assertTrue($tracker->isRunning());
        self::assertSame('First task', $tracker->current()->description);
    }

    public function testStop(): void
    {
        $tracker = new Tracker();

        $tracker->start('First task');
        $tracker->stop();

        self::assertFalse($tracker->isRunning());
        self::assertCount(1, $tracker->timeLogs());
    }
}
