<?php

namespace ClockIn\Jira;

use ClockIn\Tracker\Tracker;

interface SynchronizerInterface
{
    public function synchronize(Tracker $tracker): void;
}
