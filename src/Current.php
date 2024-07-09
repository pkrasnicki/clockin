<?php

declare(strict_types=1);

namespace Tracker;

final readonly class Current
{
    public function __construct(
        public \DateTimeImmutable $start,
        public string $description,
    ) {
    }
}
