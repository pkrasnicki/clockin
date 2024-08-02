<?php

declare(strict_types=1);

namespace ClockIn\Common;

final readonly class Period
{
    public Duration $duration;

    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end
    ) {
        $this->duration = new Duration($end->getTimestamp() - $start->getTimestamp());
    }

    public static function from(\DateTimeImmutable $start, Duration $duration): self
    {
        return new self($start, $start->add(new \DateInterval('PT'.$duration->seconds.'S')));
    }

    public function equals(Period $other): bool
    {
        return $this->start->getTimestamp() === $other->start->getTimestamp()
            && $this->end->getTimestamp() === $other->end->getTimestamp();
    }
}
