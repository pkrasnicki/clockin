<?php

declare(strict_types=1);

namespace Tracker\Common;

final readonly class Period
{
    public Duration $duration;

    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end
    ) {
        $this->duration = new Duration($end->getTimestamp() - $start->getTimestamp());
    }
}
