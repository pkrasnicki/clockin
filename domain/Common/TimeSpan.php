<?php

declare(strict_types=1);

namespace Tracker\Common;

final class TimeSpan
{
    public function __construct(
        public readonly \DateTimeImmutable $start,
        public readonly \DateTimeImmutable $end,
    ) {
    }

    public function duration(): Duration
    {
        return new Duration($this->start->diff($this->end));
    }
}
