<?php

namespace ClockIn\Jira;

use ClockIn\Tracker;

interface SynchronizerInterface
{
    public function synchronize(Tracker $tracker): void;
}
