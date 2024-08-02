<?php

declare(strict_types=1);

namespace ClockIn\Descriptor;

final class StringDescriptor implements TimeLogDescriptor
{
    public function __construct(private string $description)
    {
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
