<?php

namespace Tracker\TimeLog;

interface Repository
{
    public function get(TimeLogId $id): ?TimeLog;

    /**
     * @return iterable<TimeLog>
     */
    public function all(): iterable;
}
