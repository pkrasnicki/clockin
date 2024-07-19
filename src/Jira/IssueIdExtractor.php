<?php

namespace Tracker\Jira;

use Tracker\TimeLog;

interface IssueIdExtractor
{
    public function extract(TimeLog $timeLog): IssueId;
}
