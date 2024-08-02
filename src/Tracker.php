<?php

declare(strict_types=1);

namespace Tracker;

use Symfony\Component\Clock\ClockAwareTrait;
use Tracker\Common\Period;
use Webmozart\Assert\Assert;

final class Tracker
{
    use ClockAwareTrait;

    public function __construct(
        /**
         * @var array<TimeLog>
         */
        private array $timeLogs = [],
        private ?Current $current = null,
    ) {
        Assert::allIsInstanceOf($timeLogs, TimeLog::class);
    }

    public function start(string $description): void
    {
        Assert::null($this->current, 'Cannot start while tracker is running.');

        $this->current = new Current(
            $this->now(),
            $description,
        );
    }

    public function stop(): void
    {
        Assert::notNull($this->current, 'Cannot stop while tracker is not running.');

        $this->timeLogs[] = new TimeLog(
            TimeLogId::new(),
            new Period(
                $this->current->start,
                $this->now(),
            ),
            $this->current->description,
            $this->now(),
        );

        $this->current = null;
    }

    public function isRunning(): bool
    {
        return null !== $this->current;
    }

    public function current(): ?Current
    {
        return $this->current;
    }

    /**
     * @return array<TimeLog>
     */
    public function timeLogs(): array
    {
        return $this->timeLogs;
    }

    public function remove(TimeLogId $id): void
    {
        $this->timeLogs = array_filter(
            $this->timeLogs,
            fn (TimeLog $timeLog) => !$timeLog->id->equals($id),
        );
    }

    public function modifyPeriod(TimeLogId $id, Period $newPeriod): void
    {
        $this->timeLogs = array_map(
            fn (TimeLog $timeLog) => $timeLog->id->equals($id)
                ? new TimeLog(
                    $timeLog->id,
                    $newPeriod,
                    $timeLog->description,
                    $this->now(),
                )
                : $timeLog,
            $this->timeLogs,
        );
    }

    public function updateDescription(TimeLogId $id, string $newDescription): void
    {
        $this->timeLogs = array_map(
            fn (TimeLog $timeLog) => $timeLog->id->equals($id)
                ? new TimeLog(
                    $timeLog->id,
                    $timeLog->period,
                    $newDescription,
                    $timeLog->updatedAt,
                )
                : $timeLog,
            $this->timeLogs,
        );
    }
}
