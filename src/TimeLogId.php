<?php

declare(strict_types=1);

namespace ClockIn;

use ClockIn\Common\UuidId;

final class TimeLogId extends UuidId implements \Stringable
{
    public function equals(TimeLogId $other): bool
    {
        return $this->__toString() === $other->__toString();
    }
}
