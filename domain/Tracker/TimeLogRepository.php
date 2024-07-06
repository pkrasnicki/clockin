<?php

namespace Tracker\Tracker;

interface TimeLogRepository
{
    public function findById(TimeLogID $id): ?TimeLog;

    /**
     * @return iterable<TimeLog>
     */
    public function findAll(): iterable;
}
