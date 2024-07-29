<?php

declare(strict_types=1);

namespace Tracker;

use Tracker\Common\Period;
use Webmozart\Assert\Assert;

final class Tracker
{
    public function __construct(
        private array $timeLogs = [],
        private ?Current $current = null,
    ) {
        Assert::allIsInstanceOf($timeLogs, TimeLog::class);
    }

    public function start(string $description): void
    {
        Assert::null($this->current, 'Cannot start while tracker is running.');

        $this->current = new Current(
            new \DateTimeImmutable(),
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
                new \DateTimeImmutable(),
            ),
            $this->current->description,
            new \DateTimeImmutable(),
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
}
