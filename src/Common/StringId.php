<?php

declare(strict_types=1);

namespace ClockIn\Common;

trait StringId
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
