<?php

namespace Tracker\Jira;

use Tracker\TimeLogId;

interface SynchronizedWorkLogRepository
{
    public function get(WorkLogId $id): ?SynchronizedWorkLog;

    public function find(TimeLogId $timeLog): ?SynchronizedWorkLog;

    public function save(SynchronizedWorkLog $synchronizedWorkLog): void;
}
