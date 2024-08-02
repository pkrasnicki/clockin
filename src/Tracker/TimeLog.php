<?php

declare(strict_types=1);

namespace ClockIn\Tracker;

use ClockIn\Common\Duration;
use ClockIn\Common\Period;

final readonly class TimeLog
{
    public function __construct(
        public TimeLogId $id,
        public Period $period,
        public string $description,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function duration(): Duration
    {
        return $this->period->duration;
    }
}
