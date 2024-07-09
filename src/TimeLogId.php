<?php

declare(strict_types=1);

namespace Tracker;

final class TimeLogId implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
