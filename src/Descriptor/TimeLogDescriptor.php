<?php

declare(strict_types=1);

namespace ClockIn\Descriptor;

interface TimeLogDescriptor
{
    public function getDescription(): string;
}
