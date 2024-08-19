<?php

declare(strict_types=1);

namespace ClockIn\Tracker;

use ClockIn\Common\Period;
use Symfony\Component\Clock\DatePoint;

final class JsonFileTrackerRepository implements TrackerRepository
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

    public function load(): Tracker
    {
        if (!\file_exists($this->path)) {
            return new Tracker();
        }

        $data = \file_get_contents($this->path);
        $data = \json_decode($data, true);

        $current = null !== ($data['current'] ?? null) ? new Current(
            new DatePoint($data['current']['start']),
            $data['current']['description'],
        ) : null;

        $timeLogs = [];
        foreach ($data['timeLogs'] ?? [] as $timeLog) {
            $timeLogs[] = new TimeLog(
                new TimeLogId($timeLog['id']),
                new Period(
                    new DatePoint($timeLog['period']['start']),
                    new DatePoint($timeLog['period']['end']),
                ),
                $timeLog['description'],
                new DatePoint($timeLog['updatedAt'] ?? 'now'),
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
                'id' => (string) $timeLog->id,
                'period' => [
                    'start' => $timeLog->period->start->format(\DateTimeInterface::ATOM),
                    'end' => $timeLog->period->end->format(\DateTimeInterface::ATOM),
                ],
                'description' => $timeLog->description,
                'updatedAt' => $timeLog->updatedAt->format(\DateTimeInterface::ATOM),
            ];
        }

        \file_put_contents($this->path, \json_encode($data));
    }
}
