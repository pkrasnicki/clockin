<?php

declare(strict_types=1);

namespace Tracker;

use Tracker\Common\Period;

final readonly class TimeLog
{
    public function __construct(
        public TimeLogId $id,
        public Period $period,
        public string $description,
    ) {
    }
}
