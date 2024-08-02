<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\TimeLogId;

final class SynchronizedWorkLog
{
    public function __construct(
        public readonly WorkLogId $id,
        public readonly TimeLogId $timeLog,
        public JiraId $jiraId,
        public IssueId $issue,
        public \DateTimeImmutable $synchronizedAt,
    ) {
    }
}
