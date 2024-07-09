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
            new TimeLogId((string) \count($this->timeLogs)),
            new Period(
                $this->current->start,
                new \DateTimeImmutable(),
            ),
            $this->current->description,
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
}
