<?php

declare(strict_types=1);

namespace Tracker;

use Tracker\Common\Duration;

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
