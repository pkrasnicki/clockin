<?php

declare(strict_types=1);

namespace Tracker\Jira;

use Symfony\Component\Clock\ClockAwareTrait;
use Tracker\TimeLog;
use Tracker\TimeLogId;
use Tracker\Tracker;

final class JiraSynchronizer implements SynchronizerInterface
{
    use ClockAwareTrait;

    public function __construct(
        private ClientInterface $jira,
        private IssueIdExtractor $issueIdExtractor,
        private SynchronizedWorkLogRepository $synchronizedWorkLogRepository,
    ) {
    }

    public function synchronize(Tracker $tracker): void
    {
        $timeLogs = $tracker->timeLogs();

        foreach ($timeLogs as $timeLog) {
            $synchronizedWorkLog = $this->synchronizedWorkLogRepository->find($timeLog->id);
            if (null === $synchronizedWorkLog) {
                $this->add($timeLog);
                continue;
            }

            $this->update($synchronizedWorkLog, $timeLog);
        }

        foreach ($this->getRemovedWorkLogs($tracker) as $removedWorkLog) {
            $this->remove($removedWorkLog);
        }
    }

    private function add(TimeLog $timeLog): void
    {
        $issueId = $this->issueIdExtractor->extract($timeLog);
        $jiraId = $this->jira->addWorkLog($issueId, $timeLog->period);

        $synchronizedWorkLog = new SynchronizedWorkLog(
            WorkLogId::new(),
            timeLog: $timeLog->id,
            jiraId: $jiraId,
            issue: $issueId,
            synchronizedAt: $this->now(),
        );

        $this->synchronizedWorkLogRepository->save($synchronizedWorkLog);
    }

    private function update(SynchronizedWorkLog $synchronizedWorkLog, TimeLog $timeLog): void
    {
        $issueId = $this->issueIdExtractor->extract($timeLog);

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
        $synchronizedWorkLog->synchronizedAt = $this->now();

        $this->synchronizedWorkLogRepository->save($synchronizedWorkLog);
    }

    private function remove(SynchronizedWorkLog $synchronizedWorkLog): void
    {
        $this->jira->deleteWorkLog($synchronizedWorkLog->jiraId, $synchronizedWorkLog->issue);
        $this->synchronizedWorkLogRepository->remove($synchronizedWorkLog->id);
    }

    /**
     * @return array<SynchronizedWorkLog>
     */
    private function getRemovedWorkLogs(Tracker $tracker): array
    {
        return array_filter(
            (array) $this->synchronizedWorkLogRepository->all(),
            fn (SynchronizedWorkLog $synchronizedWorkLog) => !$this->hasTimeLog($tracker, $synchronizedWorkLog->timeLog)
        );
    }

    private static function hasTimeLog(Tracker $tracker, TimeLogId $id): bool
    {
        return !empty(array_filter(
            $tracker->timeLogs(),
            fn (TimeLog $timeLog) => $timeLog->id->equals($id)
        ));
    }
}
