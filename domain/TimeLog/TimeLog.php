<?php

declare(strict_types=1);

namespace Tracker\TimeLog;

use Tracker\Common\TimeSpan;

final class TimeLog
{
    public function __construct(
        public readonly TimeLogId $id,
        public readonly TimeSpan $timeSpan,
        public readonly string $description,
    ) {
    }
}
