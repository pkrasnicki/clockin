<?php

declare(strict_types=1);

namespace App\Repository\TimeLog;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tracker\Common\TimeSpan;
use Tracker\TimeLog\Repository as TimeLogRepositoryInterface;
use Tracker\TimeLog\TimeLog;
use Tracker\TimeLog\TimeLogId;

final class TogglRepository implements TimeLogRepositoryInterface
{
    public function __construct(private HttpClientInterface $togglClient)
    {
    }

    public function get(TimeLogId $id): ?TimeLog
    {
        $response = $this->togglClient->request('GET', sprintf('me/time_entries/%s', $id));

        try {
            return self::mapEntry(json_decode($response->getContent(), true));
        } catch (\Throwable) {
            return null;
        }
    }

    public function all(): iterable
    {
        $response = $this->togglClient->request('GET', sprintf('me/time_entries'));

        try {
            return array_filter(array_map(self::mapEntry(...), json_decode($response->getContent(), true)));
        } catch (\Throwable) {
            return [];
        }
    }

    private static function mapEntry(array $entry): ?TimeLog
    {
        if (!$entry['stop']) {
            return null;
        }

        return new TimeLog(
            new TimeLogId((string) $entry['id']),
            new TimeSpan(
                new \DateTimeImmutable($entry['start']),
                new \DateTimeImmutable($entry['stop']),
            ),
            $entry['description'],
        );
    }
}
