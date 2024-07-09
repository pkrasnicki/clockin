<?php

declare(strict_types=1);

namespace Tracker\Common;

final readonly class Period
{
    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end
    ) {
    }
}
