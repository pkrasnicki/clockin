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
        $jiraId = $this->jira->addWorkLog($issueId, $timeLog->period);

        $synchronizedWorkLog = new SynchronizedWorkLog(
            WorkLogId::new(),
            timeLog: $timeLog->id,
            jiraId: $jiraId,
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
            return;
        }

        if (!$synchronizedWorkLog->issue->equals($issueId)) {
            $this->jira->deleteWorkLog($synchronizedWorkLog->jiraId, $synchronizedWorkLog->issue);
            $synchronizedWorkLog->jiraId = $this->jira->addWorkLog($issueId, $timeLog->period);
        } else {
            $this->jira->updateWorkLog($synchronizedWorkLog->jiraId, $issueId, $timeLog->period);
        }

        $synchronizedWorkLog->issue = $issueId;
        $synchronizedWorkLog->synchronizedAt = new \DateTimeImmutable();

        $this->synchronizedWorkLogRepository->save($synchronizedWorkLog);
    }
}
