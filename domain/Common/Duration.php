<?php

declare(strict_types=1);

namespace Tracker\Common;

final class Duration
{
    public readonly int $seconds;

    public function __construct(public readonly \DateInterval $interval)
    {
        $this->seconds = static::intervalToSeconds($interval);
    }

    private static function intervalToSeconds(\DateInterval $interval): int
    {
        $from = new \DateTimeImmutable();
        $to = $from->add($interval);

        return $to->getTimestamp() - $from->getTimestamp();
    }
}
