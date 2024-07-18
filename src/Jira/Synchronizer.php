<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Tracker\TimeLog;

final class Synchronizer
{
    public function __construct(
        private SynchronizedWorkLogRepository $synchronizedWorkLogRepository,
        private Client $jira,
    ) {
    }

    public function synchronize(TimeLog $timeLog): void
    {
        $synchronizedWorkLog = $this->synchronizedWorkLogRepository->find($timeLog->id);

        if (null === $synchronizedWorkLog) {
            $this->addWorkLog($timeLog);
        } else {
            $this->updateWorkLog($synchronizedWorkLog, $timeLog);
        }
    }

    private function addWorkLog(TimeLog $timeLog): void
    {
        $issueId = IssueIdExtractor::extractIssueId($timeLog);
        $workLogId = $this->jira->addWorkLog($issueId, $timeLog->period);

        $synchronizedWorkLog = new SynchronizedWorkLog(
            id: $workLogId,
            timeLog: $timeLog->id,
            issue: $issueId,
            synchronizedAt: new \DateTimeImmutable(),
        );

        $this->synchronizedWorkLogRepository->save($synchronizedWorkLog);
    }

    private function updateWorkLog(SynchronizedWorkLog $synchronizedWorkLog, TimeLog $timeLog): void
    {
        $issueId = IssueIdExtractor::extractIssueId($timeLog);

        $needsUpdate = !$synchronizedWorkLog->issue->equals($issueId) || $synchronizedWorkLog->synchronizedAt < $timeLog->updatedAt;

        if (!$needsUpdate) {
            echo PHP_EOL . 'Work log ' . $synchronizedWorkLog->id . ' is already synchronized.' . PHP_EOL;
            return;
        }

        $this->jira->updateWorkLog($synchronizedWorkLog->id, $issueId, $timeLog->period);

        $synchronizedWorkLog->issue = $issueId;
        $synchronizedWorkLog->synchronizedAt = new \DateTimeImmutable();

        $this->synchronizedWorkLogRepository->save($synchronizedWorkLog);
    }
}
