<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\Common\Duration;
use ClockIn\Jira\Exception\IssueIdNotRecognizedException;
use ClockIn\Jira\Exception\SynchronizationException;
use ClockIn\Tracker\TimeLog;
use ClockIn\Tracker\TimeLogId;
use ClockIn\Tracker\Tracker;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpClient\Exception\ClientException;

final class Synchronizer implements SynchronizerInterface
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
        foreach ($tracker->timeLogs() as $timeLog) {

            if ($timeLog->duration()->lessThan(new Duration(60))) {
                continue;
            }

            try {
                $synchronizedWorkLog = $this->synchronizedWorkLogRepository->find($timeLog->id);
                if (null === $synchronizedWorkLog) {
                    $this->add($timeLog);
                    continue;
                }

                $this->update($synchronizedWorkLog, $timeLog);
            } catch (IssueIdNotRecognizedException) {
            } catch (ClientException $e) {
                throw new SynchronizationException($e->getMessage(), $e->getCode(), $e);
            }
        }

        foreach ($this->getRemovedWorkLogs($tracker) as $removedWorkLog) {
            try {
                $this->remove($removedWorkLog);
            } catch (IssueIdNotRecognizedException) {
            } catch (ClientException $e) {
                throw new SynchronizationException($e->getMessage(), $e->getCode(), $e);
            }
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
