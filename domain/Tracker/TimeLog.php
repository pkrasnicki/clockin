<?php

declare(strict_types=1);

namespace Tracker\Tracker;

use Tracker\Common\TimeSpan;

final readonly class TimeLog
{
    public function __construct(
        public TimeLogID $id,
        public TimeSpan $timeSpan,
        public string $description,
    ) {
    }
}
