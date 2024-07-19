<?php

namespace Tracker\Jira;

use Tracker\Common\Period;

interface ClientInterface
{
    public function addWorkLog(IssueId $issue, Period $period): JiraId;

    public function deleteWorkLog(JiraId $id, IssueId $issue): void;

    public function updateWorkLog(JiraId $id, IssueId $issue, Period $period): void;
}
