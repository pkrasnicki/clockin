<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Symfony\Component\Uid\Uuid;
use Tracker\Common\Period;

final class Client
{
    public function addWorkLog(IssueId $issue, Period $period): WorkLogId
    {
        $id = new WorkLogId(Uuid::v4()->toString());

        echo PHP_EOL.'Adding work log '.$id.' for issue '.$issue.'...'.PHP_EOL;

        return $id;
    }

    public function updateWorkLog(WorkLogId $id, IssueId $issue, Period $period): void
    {
        echo PHP_EOL.'Updating work log '.$id.' for issue '.$issue.'...'.PHP_EOL;
    }
}
