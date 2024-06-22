<?php

declare(strict_types=1);

namespace Tracker\TimeLog;

final class TimeLogId implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function equals(TimeLogId $other): bool
    {
        return (string) $this === (string) $other;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
