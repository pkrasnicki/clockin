<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Psr\Log\LoggerInterface;
use Tracker\TimeLogId;

final class DryRunSyncedRepo implements SynchronizedWorkLogRepository
{
    public function __construct(
        private SynchronizedWorkLogRepository $synchronizedWorkLogRepository
    ) {
    }

    public function get(WorkLogId $id): ?SynchronizedWorkLog
    {
        return $this->synchronizedWorkLogRepository->get($id);
    }

    public function find(TimeLogId $timeLog): ?SynchronizedWorkLog
    {
        return $this->synchronizedWorkLogRepository->find($timeLog);
    }

    public function save(SynchronizedWorkLog $synchronizedWorkLog): void
    {
        // do nothing
    }
}
