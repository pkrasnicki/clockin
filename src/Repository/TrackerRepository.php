<?php

namespace ClockIn\Repository;

use ClockIn\Tracker;

interface TrackerRepository
{
    public function load(): Tracker;

    public function save(Tracker $tracker): void;
}
