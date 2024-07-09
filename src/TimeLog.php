<?php

declare(strict_types=1);

namespace Tracker;

use Tracker\Common\Duration;
use Tracker\Common\Period;

final readonly class TimeLog
{
    public function __construct(
        public TimeLogId $id,
        public Period $period,
        public string $description,
    ) {
    }

    public function duration(): Duration
    {
        return $this->period->duration;
    }
}
