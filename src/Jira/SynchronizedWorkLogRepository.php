<?php

namespace ClockIn\Jira;

use ClockIn\TimeLogId;

interface SynchronizedWorkLogRepository
{
    public function get(WorkLogId $id): ?SynchronizedWorkLog;

    /**
     * @return iterable<SynchronizedWorkLog>
     */
    public function all(): iterable;

    public function find(TimeLogId $timeLog): ?SynchronizedWorkLog;

    public function save(SynchronizedWorkLog $synchronizedWorkLog): void;

    public function remove(WorkLogId $id): void;
}
