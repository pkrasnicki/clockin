<?php

namespace Tracker\Repository;

use Tracker\Tracker;

interface TrackerRepository
{
    public function load(): Tracker;

    public function save(Tracker $tracker): void;
}
