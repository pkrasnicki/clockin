<?php

declare(strict_types=1);

namespace Tracker\Repository;

use Tracker\Common\Period;
use Tracker\Current;
use Tracker\TimeLog;
use Tracker\TimeLogId;
use Tracker\Tracker;
use Tracker\TrackerRepository;

final class JsonFileTrackerRepository implements TrackerRepository
{
    public function __construct(private string $path)
    {
    }

    public function load(): Tracker
    {
        if (!\file_exists($this->path)) {
            return new Tracker();
        }

        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        $current = null !== ($data['current'] ?? null) ? new Current(
            new \DateTimeImmutable($data['current']['start']),
            $data['current']['description'],
        ) : null;

        $timeLogs = [];
        foreach ($data['timeLogs'] ?? [] as $timeLog) {
            $timeLogs[] = new TimeLog(
                new TimeLogId($timeLog['id']),
                new Period(
                    new \DateTimeImmutable($timeLog['period']['start']),
                    new \DateTimeImmutable($timeLog['period']['end']),
                ),
                $timeLog['description'],
            );
        }

        return new Tracker($timeLogs, $current);
    }

    public function save(Tracker $tracker): void
    {
        $data = [
            'timeLogs' => [],
        ];

        $data['current'] = $tracker->current() ? [
            'start' => $tracker->current()->start->format(\DateTimeInterface::ATOM),
            'description' => $tracker->current()->description,
        ] : null;

        foreach ($tracker->timeLogs() as $timeLog) {
            $data['timeLogs'][] = [
                'id' => (string)$timeLog->id,
                'period' => [
                    'start' => $timeLog->period->start->format(\DateTimeInterface::ATOM),
                    'end' => $timeLog->period->end->format(\DateTimeInterface::ATOM),
                ],
                'description' => $timeLog->description,
            ];
        }

        \file_put_contents($this->path, \json_encode($data));
    }
}
