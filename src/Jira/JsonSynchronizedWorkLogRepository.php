<?php

declare(strict_types=1);

namespace ClockIn\Jira;

use ClockIn\TimeLogId;

final class JsonSynchronizedWorkLogRepository implements SynchronizedWorkLogRepository
{
    public function __construct(private string $path)
    {
        if (!file_exists(\dirname($path))) {
            \mkdir(\dirname($path), 0600, true);
        }
        if (!file_exists($path)) {
            file_put_contents($path, '{}');
        }
    }

    public function get(WorkLogId $id): ?SynchronizedWorkLog
    {
        return $this->findBy('id', (string) $id);
    }

    public function find(TimeLogId $timeLog): ?SynchronizedWorkLog
    {
        return $this->findBy('timeLog', (string) $timeLog);
    }

    private function findBy(string $field, mixed $value): ?SynchronizedWorkLog
    {
        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        foreach ($data as $workLog) {
            if ($workLog[$field] == $value) {
                return self::create($workLog);
            }
        }

        return null;
    }

    public function save(SynchronizedWorkLog $synchronizedWorkLog): void
    {
        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        // remove previous value
        $data = array_filter($data, fn ($workLog) => $workLog['id'] !== (string) $synchronizedWorkLog->id);

        $data[] = [
            'id' => (string) $synchronizedWorkLog->id,
            'timeLog' => (string) $synchronizedWorkLog->timeLog,
            'jiraId' => (string) $synchronizedWorkLog->jiraId,
            'issue' => (string) $synchronizedWorkLog->issue,
            'synchronizedAt' => $synchronizedWorkLog->synchronizedAt->format(\DateTimeInterface::ATOM),
        ];

        \file_put_contents($this->path, \json_encode($data));
    }

    public function all(): iterable
    {
        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        return array_map(fn ($workLog) => self::create($workLog), $data);
    }

    private static function create(array $data): SynchronizedWorkLog
    {
        return new SynchronizedWorkLog(
            new WorkLogId($data['id']),
            new TimeLogId($data['timeLog']),
            new JiraId($data['jiraId']),
            new IssueId($data['issue']),
            new \DateTimeImmutable($data['synchronizedAt']),
        );
    }

    public function remove(WorkLogId $id): void
    {
        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        $data = array_filter($data, fn ($workLog) => $workLog['id'] !== (string) $id);

        \file_put_contents($this->path, \json_encode($data));
    }
}
