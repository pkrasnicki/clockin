<?php

namespace Tests\Jira;

use ClockIn\Common\Duration;
use ClockIn\Common\Period;
use ClockIn\Jira\IssueId;
use ClockIn\Jira\Synchronizer;
use ClockIn\Tracker\TimeLog;
use ClockIn\Tracker\TimeLogId;
use ClockIn\Tracker\Tracker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class SynchronizerTest extends TestCase
{
    use ClockSensitiveTrait;

    public function testAddsWorkLogs(): void
    {
        // Given

        $tracker = new Tracker([
            new TimeLog(
                $timeLogId = TimeLogId::new(),
                $period = Period::from(new \DateTimeImmutable('2024-08-01T09:00:00'), new Duration(60 * 45)),
                description: 'ABC-1',
                updatedAt: $period->end,
            ),
        ]);

        $jiraClientSpy = new JiraClientSpy();
        $issueIdExtractor = (new IssueIdExtractorMock())->willExtract($issueId = new IssueId('ABC-1'));
        $synchronizedWorkLogsRepository = new InMemorySynchronizedWorkLogRepository();

        // When

        $synchronizer = new Synchronizer($jiraClientSpy, $issueIdExtractor, $synchronizedWorkLogsRepository);
        $synchronizer->synchronize($tracker);

        // Then

        $synchronizedTimeLog = $synchronizedWorkLogsRepository->find($timeLogId);

        $this->assertEquals($issueId, $synchronizedTimeLog->issue);
        $this->assertTrue($jiraClientSpy->added->contains([
            'issue' => $issueId,
            'period' => $period,
        ]));
    }

    public function testAddsOnlyNewWorkLogs(): void
    {
        // Given

        $tracker = new Tracker([
            new TimeLog(
                TimeLogId::new(),
                Period::from(new \DateTimeImmutable('2024-08-01T09:00:00'), new Duration(60 * 45)), // 45 minutes
                description: 'ABC-1',
                updatedAt: new \DateTimeImmutable('2024-08-01T09:45:00'),
            ),
        ]);

        $clock = static::mockTime($clockStart = new \DateTimeImmutable('2024-08-01T10:00:00'));
        $tracker->setClock($clock);

        $jiraClientSpy = new JiraClientSpy();
        $issueIdExtractor = (new IssueIdExtractorMock())->willExtract($issueId = new IssueId('ABC-1'));
        $synchronizedWorkLogsRepository = new InMemorySynchronizedWorkLogRepository();

        $synchronizer = new Synchronizer($jiraClientSpy, $issueIdExtractor, $synchronizedWorkLogsRepository);
        $synchronizer->synchronize($tracker);

        $jiraClientSpy->clear();

        // When

        $tracker->start('ABC-1');
        $clock->sleep(60 * 60); // 1 hour
        $tracker->stop();

        $synchronizer->synchronize($tracker);

        // Then

        $this->assertCount(1, $jiraClientSpy->added);
        $this->assertTrue(
            $jiraClientSpy->added->contains(
                fn ($workLog) => $workLog['issue']->equals($issueId) && $workLog['period']->equals(
                    Period::from($clockStart, new Duration(60 * 60))
                )
            )
        );
    }

    public function testRemovesWorkLogs(): void
    {
        // Given

        $clock = static::mockTime();

        $tracker = new Tracker();

        $tracker->start('ABC-1');
        $clock->sleep(60 * 45);
        $tracker->stop();

        $jiraClientSpy = new JiraClientSpy();
        $issueIdExtractor = (new IssueIdExtractorMock())->willExtract($issueId = new IssueId('ABC-1'));

        $synchronizer = new Synchronizer(
            $jiraClientSpy,
            $issueIdExtractor,
            new InMemorySynchronizedWorkLogRepository()
        );

        $synchronizer->synchronize($tracker);
        $jiraClientSpy->clear();

        // When

        /** @var TimeLogId $timeLogId */
        $timeLogId = collect($tracker->timeLogs())->first()->id;
        $tracker->remove($timeLogId);

        $synchronizer->synchronize($tracker);

        // Then

        $this->assertCount(1, $jiraClientSpy->deleted);
        $this->assertTrue(
            $jiraClientSpy->deleted->contains(fn ($workLog) => $workLog['issue'] == $issueId)
        );
    }

    public function testUpdatesExistingWorkLogs(): void
    {
        // Given

        $clock = static::mockTime(new \DateTimeImmutable('2024-08-01T09:00:00'));

        $tracker = new Tracker();

        $tracker->start('ABC-1');
        $clock->sleep(60 * 45);
        $tracker->stop();

        $jiraClientSpy = new JiraClientSpy();
        $issueIdExtractor = (new IssueIdExtractorMock())->willExtract($issueId = new IssueId('ABC-1'));

        $synchronizer = new Synchronizer(
            $jiraClientSpy,
            $issueIdExtractor,
            new InMemorySynchronizedWorkLogRepository()
        );

        $synchronizer->synchronize($tracker);
        $jiraClientSpy->clear();

        $clock->sleep(60);

        // When

        /** @var TimeLogId $timeLogId */
        $timeLogId = collect($tracker->timeLogs())->first()->id;

        $tracker->modifyPeriod(
            $timeLogId,
            Period::from(new \DateTimeImmutable('2024-07-31T09:00:00'), new Duration(60 * 60))
        );

        $synchronizer->synchronize($tracker);

        // Then

        $this->assertCount(1, $jiraClientSpy->updated);
        $this->assertTrue(
            $jiraClientSpy->updated->contains(
                fn ($workLog) => $workLog['issue'] == $issueId && $workLog['period']->equals(
                    Period::from(new \DateTimeImmutable('2024-07-31T09:00:00'), new Duration(60 * 60))
                )
            )
        );
    }
}
