<?php

declare(strict_types=1);

namespace ClockIn\Common;

use Symfony\Component\Uid\Uuid;

abstract class UuidId
{
    use StringId;

    public static function new(): static
    {
        return new static(Uuid::v4()->toString());
    }
}
