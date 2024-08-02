<?php

namespace Tests\Tracker;

use ClockIn\Common\Duration;
use ClockIn\Common\Period;
use ClockIn\Tracker\TimeLog;
use ClockIn\Tracker\Tracker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class TrackerTest extends TestCase
{
    use ClockSensitiveTrait;

    public function testStart(): void
    {
        // Given

        $clock = static::mockTime();
        $start = $clock->now();

        $tracker = new Tracker();

        // When

        $tracker->start('First task');

        // Then

        self::assertTrue($tracker->isRunning());
        self::assertSame('First task', $tracker->current()->description);
        self::assertEquals($start, $tracker->current()->start);
    }

    public function testStop(): void
    {
        // Given

        $clock = static::mockTime();
        $start = $clock->now();

        $tracker = new Tracker();

        // When

        $tracker->start('First task');
        $clock->sleep(60 * 45);
        $tracker->stop();

        self::assertFalse($tracker->isRunning());
        self::assertCount(1, $tracker->timeLogs());
        self::assertEquals($start, $tracker->timeLogs()[0]->period->start);
        self::assertEquals($start->modify('+45 minutes'), $tracker->timeLogs()[0]->period->end);
    }

    public function testRemovesTimeLog(): void
    {
        // Given

        $clock = static::mockTime();

        $tracker = new Tracker();

        for ($n = 0; $n < 10; ++$n) {
            $tracker->start("Task $n");
            $clock->sleep(60 * 45);
            $tracker->stop();
        }

        // When

        foreach ($tracker->timeLogs() as $timeLog) {
            $tracker->remove($timeLog->id);
            break;
        }

        // Then

        self::assertCount(9, $tracker->timeLogs());
    }

    public function testModifiesPeriod(): void
    {
        // Given

        $clock = static::mockTime();
        $start = $clock->now();

        $tracker = new Tracker();

        $tracker->start('First task');
        $clock->sleep(60 * 45);
        $tracker->stop();

        // When

        $timeLogId = collect($tracker->timeLogs())->first()->id;
        $tracker->modifyPeriod($timeLogId, new Period($start, $start->modify('+1 hour')));

        // Then

        /** @var TimeLog $timeLog */
        $timeLog = collect($tracker->timeLogs())->first(fn(TimeLog $timeLog) => $timeLog->id->equals($timeLogId));
        self::assertTrue($timeLog->duration()->equals(new Duration(60 * 60)));
    }

    public function testUpdatesDescription(): void
    {
        // Given

        $clock = static::mockTime();
        $start = $clock->now();

        $tracker = new Tracker();

        $tracker->start('First task');
        $clock->sleep(60 * 45);
        $tracker->stop();

        // When

        $timeLogId = collect($tracker->timeLogs())->first()->id;
        $tracker->updateDescription($timeLogId, 'Updated description');

        // Then

        $timeLog = collect($tracker->timeLogs())->first(fn(TimeLog $timeLog) => $timeLog->id->equals($timeLogId));
        self::assertSame('Updated description', $timeLog->description);
    }
}
