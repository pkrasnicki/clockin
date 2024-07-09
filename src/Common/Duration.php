<?php

declare(strict_types=1);

namespace Tracker\Common;

final class Duration implements \Stringable
{
    public function __construct(private int $seconds)
    {
    }

    public function __toString(): string
    {
        $hours = \floor($this->seconds / 3600);
        $minutes = \floor(($this->seconds % 3600) / 60);
        $seconds = $this->seconds % 60;

        return \sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
