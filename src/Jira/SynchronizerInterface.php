<?php

namespace Tracker\Jira;

use Tracker\Tracker;

interface SynchronizerInterface
{
    public function synchronize(Tracker $tracker): void;
}
