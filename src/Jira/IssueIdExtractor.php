<?php

namespace ClockIn\Jira;

use ClockIn\TimeLog;

interface IssueIdExtractor
{
    public function extract(TimeLog $timeLog): IssueId;
}
