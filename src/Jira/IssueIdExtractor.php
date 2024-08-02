<?php

namespace ClockIn\Jira;

use ClockIn\Tracker\TimeLog;

interface IssueIdExtractor
{
    public function extract(TimeLog $timeLog): IssueId;
}
