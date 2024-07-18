<?php

declare(strict_types=1);

namespace Tracker\Common;

use Symfony\Component\Uid\Uuid;

abstract class UuidId
{
    use StringId;

    public function new(): static
    {
        return new static(Uuid::v4()->toString());
    }
}
