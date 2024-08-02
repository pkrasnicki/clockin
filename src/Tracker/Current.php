<?php

declare(strict_types=1);

namespace ClockIn\Tracker;

use ClockIn\Common\Duration;

final readonly class Current
{
    public function __construct(
        public \DateTimeImmutable $start,
        public string $description,
    ) {
    }

    public function duration(): Duration
    {
        return new Duration(\time() - $this->start->getTimestamp());
    }
}
