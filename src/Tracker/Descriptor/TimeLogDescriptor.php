<?php

declare(strict_types=1);

namespace ClockIn\Tracker\Descriptor;

interface TimeLogDescriptor
{
    public function getDescription(): string;
}
