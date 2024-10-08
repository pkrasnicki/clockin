<?php

declare(strict_types=1);

namespace Tests\Jira;

use ClockIn\Jira\SynchronizedWorkLog;
use ClockIn\Jira\SynchronizedWorkLogRepository;
use ClockIn\Jira\WorkLogId;
use ClockIn\Tracker\TimeLogId;

final class InMemorySynchronizedWorkLogRepository implements SynchronizedWorkLogRepository
{
    private array $workLogs = [];

    public function get(WorkLogId $id): ?SynchronizedWorkLog
    {
        return $this->workLogs[(string) $id] ?? null;
    }

    public function all(): iterable
    {
        return $this->workLogs;
    }

    public function find(TimeLogId $timeLog): ?SynchronizedWorkLog
    {
        foreach ($this->workLogs as $workLog) {
            if ($workLog->timeLog->equals($timeLog)) {
                return $workLog;
            }
        }

        return null;
    }

    public function save(SynchronizedWorkLog $synchronizedWorkLog): void
    {
        $this->workLogs[(string) $synchronizedWorkLog->id] = $synchronizedWorkLog;
    }

    public function remove(WorkLogId $id): void
    {
        unset($this->workLogs[(string) $id]);
    }
}
