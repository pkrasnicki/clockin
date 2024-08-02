<?php

namespace Tests\Jira;

use PHPUnit\Framework\TestCase;
use Tracker\Jira\IssueId;
use Tracker\Jira\JiraId;
use Tracker\Jira\JsonSynchronizedWorkLogRepository;
use Tracker\Jira\SynchronizedWorkLog;
use Tracker\Jira\WorkLogId;
use Tracker\TimeLogId;

class JsonSynchronizedWorkLogRepositoryTest extends TestCase
{
    private const string FILE_PATH = __DIR__.'/data';

    protected function tearDown(): void
    {
        if (file_exists(self::FILE_PATH)) {
            unlink(self::FILE_PATH);
        }
    }

    public function testSave(): void
    {
        $repository = new JsonSynchronizedWorkLogRepository(self::FILE_PATH);
        $workLog = new SynchronizedWorkLog(
            $id = WorkLogId::new(),
            TimeLogId::new(),
            new JiraId('1'),
            IssueId::new(),
            new \DateTimeImmutable('2021-01-01 00:00:00'),
        );

        $repository->save($workLog);

        $this->assertEquals($workLog, $repository->get($id));
    }

    public function testRemove(): void
    {
        $repository = new JsonSynchronizedWorkLogRepository(self::FILE_PATH);
        $workLog = new SynchronizedWorkLog(
            $id = WorkLogId::new(),
            TimeLogId::new(),
            new JiraId('1'),
            IssueId::new(),
            new \DateTimeImmutable('2021-01-01 00:00:00'),
        );

        $repository->save($workLog);
        $repository->remove($id);

        $this->assertNull($repository->get($id));
    }
}
